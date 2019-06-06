<?php

namespace App\Events;

use App\Models\User;

class ChallengeRemoved
{
    public $user;

    public $opponent;

    /**
     * ChallengeRemoved constructor.
     *
     * @param User $user
     * @param User $opponent
     */
    public function __construct(User $user, User $opponent)
    {
        $this->user     = $user;
        $this->opponent = $opponent;
    }
}
