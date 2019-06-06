<?php

namespace App\Events;

use App\Models\User;

class ChallengeCreated
{
    public $user;

    public $opponent;

    /**
     * ChallengeCreated constructor.
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
