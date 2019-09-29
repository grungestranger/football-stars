<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'speed', 'acceleration', 'coordination', 'power',
        'accuracy', 'vision', 'reaction', 'in_gate', 'on_out',
    ];

    /**
     * Get the relation to player_roles.
     *
     * @return HasMany
     */
    public function playerRoles(): HasMany
    {
        return $this->hasMany(PlayerRole::class);
    }

    /**
     * Get the relation to player_schemas.
     *
     * @return HasMany
     */
    public function playerSchemas(): HasMany
    {
        return $this->hasMany(PlayerSchema::class);
    }
}
