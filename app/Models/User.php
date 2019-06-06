<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'online', 'is_match'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_match'];

    /**
     * Get the relation to schemas.
     *
     * @return HasMany
     */
    public function schemas(): HasMany
    {
        return $this->hasMany(UserSchema::class)->orderBy('id');
    }

    /**
     * Get the relation to players.
     *
     * @return HasMany
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Create default schema and players.
     */
    public function createTeam()
    {
        lockForUpdate(function () {
            if ($this->schemas()->first()) {
                throw ValidationException::withMessages([trans('common.team_already_exists')]);
            }

            $schema = $this->schemas()->create([
                'name'     => config('schema.defaultName'),
                'settings' => json_encode(config('schema.defaultSettings')),
            ]);

            $roles    = array_flip(config('player.roles'));
            $names    = config('player.names');
            $surnames = config('player.surnames');

            $reserveIndex = 0;

            foreach (config('player.roles_data') as $key => $val) {
                $defaultPosCount = 0;

                for ($i = 0; $i < $val['count']; $i++) {
                    // Player.

                    $data = [
                        'name' => $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)],
                    ];

                    foreach ($val['dataRange'] as $key1 => $val1) {
                        $data[$key1] = rand($val1[0], $val1[1]);
                    }

                    $player = $this->players()->create($data);

                    // Roles.

                    $data = [
                        [
                            'player_id' => $player->id,
                            'role_id'   => $roles[$key],
                        ],
                    ];

                    $addRolesCount = rand(0, config('player.add_roles_max_count'));
                    $addRoles      = $val['addRoles'];

                    while ($addRolesCount && count($addRoles)) {
                        $addRoleKey = array_rand($addRoles);

                        $data[] = [
                            'player_id' => $player->id,
                            'role_id'   => $roles[$addRoles[$addRoleKey]],
                        ];

                        unset($addRoles[$addRoleKey]);
                        $addRolesCount--;
                    }

                    $player->roles()->insert($data);

                    // Settings.

                    if (isset($val['defaultPos']) && $defaultPosCount < count($val['defaultPos'])) {
                        $defSet = [
                            'position'     => $val['defaultPos'][$defaultPosCount],
                            'reserveIndex' => NULL,
                        ];
                        $defaultPosCount++;
                    } else {
                        $defSet = [
                            'position'     => NULL,
                            'reserveIndex' => $reserveIndex,
                        ];
                        $reserveIndex++;
                    }

                    $player->settings()->create([
                        'schema_id' => $schema->id,
                        'settings'  => json_encode($defSet),
                    ]);
                }
            }
        }, $this);
    }

    /**
     * Get the relation to challenges by field from_user_id.
     *
     * @return HasMany
     */
    public function fromChallenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'from_user_id')
            ->orderBy('created_at', 'desc')
            ->with('toUser');
    }

    /**
     * Get the relation to challenges by field to_user_id.
     *
     * @return HasMany
     */
    public function toChallenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'to_user_id')
            ->orderBy('created_at', 'desc')
            ->with('fromUser');
    }

    /**
     * Get all users with match relations.
     *
     * @return Collection
     */
    public static function getList(): Collection
    {
        return static::with(['match1', 'match2'])->get();
    }

    /**
     * Get the relation to matches by field user1_id.
     *
     * @return HasMany
     */
    public function match1(): HasMany
    {
        return $this->hasMany(Match::class, 'user1_id')
            ->whereNull('result');
    }

    /**
     * Get the relation to matches by field user2_id.
     *
     * @return HasMany
     */
    public function match2(): HasMany
    {
        return $this->hasMany(Match::class, 'user2_id')
            ->whereNull('result');
    }

    /**
     * @return Match|null
     */
    public function getMatchAttribute(): ?Match
    {
        return $this->match1->first() ?: $this->match2->first();
    }

    /**
     * @return bool
     */
    public function getIsMatchAttribute(): bool
    {
        return (bool) $this->match;
    }

    /**
     * @return User|null
     */
    public function getMatchOpponentAttribute(): ?User
    {
        return $this->match ? ($this->match->user1_id == $this->id ? $this->match->user2 : $this->match->user1) : null;
    }

    /**
     * @return bool
     */
    public function getIsManAttribute(): bool
    {
        return $this->type == 'man';
    }

    /**
     * Set the field "onine" to "0" for all users with type "man".
     */
    public static function resetOnline()
    {
        static::where([['type', 'man'], ['online', '!=', 0]])->update(['online' => 0]);
    }

    /**
     *
     */
    public function setOnline()
    {
        $this->online = 1;

        $this->save();
    }

    /**
     *
     */
    public function unsetOnline()
    {
        $this->online         = 0;
        $this->last_online_at = Carbon::now()->toDateTimeString();

        $this->save();
    }

    /**
     * Create a challenge.
     *
     * @param self $opponent
     */
    public function createChallenge(self $opponent)
    {
        lockForUpdate(function () use ($opponent) {
            if (
                $this->fromChallenges()->where('to_user_id', $opponent->id)->exists()
                || $this->toChallenges()->where('from_user_id', $opponent->id)->exists()
            ) {
                throw ValidationException::withMessages([trans('common.challenge_already_exists')]);
            }

            $this->fromchallenges()->create(['to_user_id' => $opponent->id]);
        }, $this, $opponent);
    }

    /**
     * Remove the challenge.
     *
     * @param self $opponent
     */
    public function removeChallenge(self $opponent)
    {
        lockForUpdate(function () use ($opponent) {
            if ($this->fromChallenges()->where('to_user_id', $opponent->id)->exists()) {
                $this->fromChallenges()->where('to_user_id', $opponent->id)->delete();
            } elseif ($this->toChallenges()->where('from_user_id', $opponent->id)->exists()) {
                $this->toChallenges()->where('from_user_id', $opponent->id)->delete();
            } else {
                throw ValidationException::withMessages([trans('common.challenge_is_not_found')]);
            }
        }, $this, $opponent);
    }
}
