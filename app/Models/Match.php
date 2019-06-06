<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Match extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user2_id'];

    /**
     * The name of the "updated at" column.
     *
     * @var null
     */
    const UPDATED_AT = null;

    /**
     * Get the relation to users by field user1_id.
     *
     * @return BelongsTo
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Get the relation to users by field user2_id.
     *
     * @return BelongsTo
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
