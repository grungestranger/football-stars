<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\User;

class UserService
{
    /**
     * Get all users with match relations.
     *
     * @return Collection
     */
    public function getList(): Collection
    {
        return User::with(['match1', 'match2'])->get();
    }

    /**
     * Set the field "onine" to "0" for all users with type "man".
     */
    public function resetOnline()
    {
        User::where([['type', User::TYPE_MAN], ['online', '!=', 0]])->update(['online' => 0]);
    }
}
