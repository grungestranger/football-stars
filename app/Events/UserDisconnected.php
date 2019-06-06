<?php

namespace App\Events;

use App\Models\User;

class UserDisconnected
{
    public $user;

    /**
     * UserDisconnected constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
