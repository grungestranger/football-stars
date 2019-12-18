<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Match
 *
 * @property int $id
 * @property int $user1_id
 * @property int $user2_id
 * @property string|null $result
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User $user1
 * @property-read \App\Models\User $user2
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match whereUser1Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Match whereUser2Id($value)
 * @mixin \Eloquent
 */
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
