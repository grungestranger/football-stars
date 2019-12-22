<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\MatchService;
use App\Models\Match;

class MatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var Match
     */
    protected $match;

    /**
     * Create a new job instance.
     *
     * @param Match $match
     */
    public function __construct(Match $match)
    {
        $this->match = $match;
    }

    /**
     * Execute the job.
     *
     * @param MatchService $matchService
     */
    public function handle(MatchService $matchService)
    {
        //
    }
}
