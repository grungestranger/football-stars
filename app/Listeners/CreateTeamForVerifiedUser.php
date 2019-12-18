<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Services\TeamService;

class CreateTeamForVerifiedUser
{
    /** @var TeamService */
    protected $teamService;

    /**
     * Create the event listener.
     *
     * @param TeamService $teamService
     * @return void
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Handle the event.
     *
     * @param  Verified  $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $this->teamService->createTeam($event->user);
    }
}
