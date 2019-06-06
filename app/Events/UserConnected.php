<?php

namespace App\Events;

use App\Models\User;

class UserConnected
{
    public $user;

    /**
     * UserConnected constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
