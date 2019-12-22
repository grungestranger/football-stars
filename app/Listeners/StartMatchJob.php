<?php

namespace App\Listeners;

use App\Events\MatchCreated;
use App\Jobs\MatchJob;

class StartMatchJob
{
    /**
     * Handle the event.
     *
     * @param  MatchCreated  $event
     */
    public function handle(MatchCreated $event)
    {
        MatchJob::dispatch($event->match);
    }
}
