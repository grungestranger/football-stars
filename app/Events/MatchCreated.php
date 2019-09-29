<?php

namespace App\Events;

use App\Models\User;
use App\Models\Match;

class MatchCreated
{
    public $user;

    public $opponent;

    public $match;

    /**
     * MatchCreated constructor.
     *
     * @param User $user
     * @param User $opponent
     */
    public function __construct(User $user, User $opponent, Match $match)
    {
        $this->user     = $user;
        $this->opponent = $opponent;
        $this->match    = $match;
    }
}
