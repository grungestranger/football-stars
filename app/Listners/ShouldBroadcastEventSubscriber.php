<?php

namespace App\Listeners;

use Illuminate\Events\Dispatcher;
use Redis;
use App\Events\UserConnected;
use App\Events\UserDisconnected;
use App\Events\ChallengeCreated;
use App\Events\ChallengeRemoved;
use App\Events\MatchCreated;

class ShouldBroadcastEventSubscriber
{
    /**
     * Handle user connect events.
     *
     * @param UserConnected $event
     */
    public function onUserConnected(UserConnected $event) {
        Redis::publish('all', json_encode([
            'event'  => 'userConnected',
            'userId' => $event->user->id,
        ]));
    }

    /**
     * Handle user disconnect events.
     *
     * @param UserDisconnected $event
     */
    public function onUserDisconnected(UserDisconnected $event) {
        Redis::publish('all', json_encode([
            'event'  => 'userDisconnected',
            'userId' => $event->user->id,
        ]));
    }

    /**
     * Handle creating of challenges events.
     *
     * @param ChallengeCreated $event
     */
    public function onChallengeCreated(ChallengeCreated $event) {
        if ($event->user->is_man) {
            Redis::publish('user:' . $event->user->id, json_encode([
                'event' => 'fromChallengeCreated',
                'user'  => $event->opponent,
            ]));
        }

        if ($event->opponent->is_man) {
            Redis::publish('user:' . $event->opponent->id, json_encode([
                'event' => 'toChallengeCreated',
                'user'  => $event->user,
            ]));
        }
    }

    /**
     * Handle removing of challenges events.
     *
     * @param ChallengeRemoved $event
     */
    public function onChallengeRemoved(ChallengeRemoved $event) {
        if ($event->user->is_man) {
            Redis::publish('user:' . $event->user->id, json_encode([
                'event'  => 'challengeRemoved',
                'userId' => $event->opponent->id,
            ]));
        }

        if ($event->opponent->is_man) {
            Redis::publish('user:' . $event->opponent->id, json_encode([
                'event'  => 'challengeRemoved',
                'userId' => $event->user->id,
            ]));
        }
    }

    /**
     * Handle starting of match.
     *
     * @param MatchCreated $event
     */
    public function onMatchCreated(MatchCreated $event) {
        // todo
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(UserConnected::class, static::class . '@onUserConnected');

        $events->listen(UserDisconnected::class, static::class . '@onUserDisconnected');

        $events->listen(ChallengeCreated::class, static::class . '@onChallengeCreated');

        $events->listen(ChallengeRemoved::class, static::class . '@onChallengeRemoved');

        $events->listen(MatchCreated::class, static::class . '@onMatchCreated');
    }
}
