<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    const TYPE_BOT = 'bot';
    const TYPE_MAN = 'man';

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
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('verified', function (Builder $builder) {
            $builder->whereNotNull('email_verified_at');
        });
    }

    /**
     * Get the relation to schemas.
     *
     * @return HasMany
     */
    public function schemas(): HasMany
    {
        return $this->hasMany(Schema::class)->orderBy('id');
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
        return $this->type == static::TYPE_MAN;
    }

    /**
     * @return Schema|null
     */
    public function getCurrentSchemaAttribute(): ?Schema
    {
        if ($this->last_schema_id) {
            $schema = $this->schemas()->find($this->last_schema_id);

            if ($schema) {
                return $schema;
            }
        }

        return $this->schemas()->first();
    }

    /**
     * Set the field "onine" to "0" for all users with type "man".
     */
    public static function resetOnline()
    {
        static::where([['type', static::TYPE_MAN], ['online', '!=', 0]])->update(['online' => 0]);
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
}
