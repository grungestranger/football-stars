<?php

namespace App\Events;

use App\Models\Match;

class MatchCreated
{
    public $match;

    /**
     * MatchCreated constructor.
     *
     * @param Match $match
     */
    public function __construct(Match $match)
    {
        $this->match = $match;
    }
}
