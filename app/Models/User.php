<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Exception;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $online
 * @property Carbon|null $last_online_at
 * @property int|null $last_schema_id
 * @property-read Collection|Challenge[] $fromChallenges
 * @property-read int|null $from_challenges_count
 * @property-read Schema $current_schema
 * @property-read bool $is_man
 * @property-read bool $is_match
 * @property-read Match|null $match
 * @property-read User|null $match_opponent
 * @property-read Collection|Match[] $match1
 * @property-read int|null $match1_count
 * @property-read Collection|Match[] $match2
 * @property-read int|null $match2_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Player[] $players
 * @property-read int|null $players_count
 * @property-read Collection|Schema[] $schemas
 * @property-read int|null $schemas_count
 * @property-read Collection|Challenge[] $toChallenges
 * @property-read int|null $to_challenges_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastOnlineAt($value)
 * @method static Builder|User whereLastSchemaId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereOnline($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 */
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_online_at'];

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
     *
     *
     * @return Schema
     * @throws Exception
     */
    public function getCurrentSchemaAttribute(): Schema
    {
        if ($this->last_schema_id) {
            $schema = $this->schemas()->find($this->last_schema_id);
        }

        if (empty($schema)) {
            $schema = $this->schemas()->first();
        }

        if (!$schema) {
            throw new Exception('User don\'t have schema');
        }

        /** @var Schema $schema */
        return $schema;
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
        $this->last_online_at = $this->freshTimestamp();

        $this->save();
    }

    /**
     * @param int $schemaId
     */
    public function setLastSchemaId(int $schemaId)
    {
        $this->last_schema_id = $schemaId;

        $this->save();
    }
}
