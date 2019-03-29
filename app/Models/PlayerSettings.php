<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerSettings extends Model
{
	use TableName;

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
    protected $fillable = ['schema_id', 'settings'];
}
