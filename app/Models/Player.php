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
     * Get the relation to roles.
     *
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(PlayerRoles::class);
    }

    /**
     * Get the relation to settings.
     *
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(PlayerSettings::class);
    }
}
