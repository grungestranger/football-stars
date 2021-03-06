<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerRole extends Model
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
    protected $fillable = ['player_id', 'role_id'];
}
