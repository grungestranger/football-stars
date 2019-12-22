<?php

namespace App\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Redis;
use Illuminate\Auth\Events\Verified;
use App\Events\UserConnected;
use App\Events\UserDisconnected;
use App\Events\ChallengeCreated;
use App\Events\ChallengeRemoved;
use App\Events\MatchCreated;

class ShouldBroadcastEventSubscriber
{
    /** @var string */
    protected $allChannel;

    /** @var string */
    protected $userChannelPrefix;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->allChannel        = env('ALL_CHANNEL');
        $this->userChannelPrefix = env('USER_CHANNEL_PREFIX');
    }

    /**
     * Handle user verified events.
     *
     * @param Verified $event
     */
    public function onUserVerified(Verified $event)
    {
        Redis::publish($this->allChannel, json_encode([
            'event' => 'userVerified',
            'user'  => $event->user,
        ]));
    }

    /**
     * Handle user connect events.
     *
     * @param UserConnected $event
     */
    public function onUserConnected(UserConnected $event)
    {
        Redis::publish($this->allChannel, json_encode([
            'event'  => 'userConnected',
            'userId' => $event->user->id,
        ]));
    }

    /**
     * Handle user disconnect events.
     *
     * @param UserDisconnected $event
     */
    public function onUserDisconnected(UserDisconnected $event)
    {
        Redis::publish($this->allChannel, json_encode([
            'event'  => 'userDisconnected',
            'userId' => $event->user->id,
        ]));
    }

    /**
     * Handle creating of challenges events.
     *
     * @param ChallengeCreated $event
     */
    public function onChallengeCreated(ChallengeCreated $event)
    {
        if ($event->user->is_man) {
            Redis::publish($this->userChannelPrefix . $event->user->id, json_encode([
                'event' => 'fromChallengeCreated',
                'user'  => $event->opponent,
            ]));
        }

        if ($event->opponent->is_man) {
            Redis::publish($this->userChannelPrefix . $event->opponent->id, json_encode([
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
    public function onChallengeRemoved(ChallengeRemoved $event)
    {
        if ($event->user->is_man) {
            Redis::publish($this->userChannelPrefix . $event->user->id, json_encode([
                'event'  => 'challengeRemoved',
                'userId' => $event->opponent->id,
            ]));
        }

        if ($event->opponent->is_man) {
            Redis::publish($this->userChannelPrefix . $event->opponent->id, json_encode([
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
    public function onMatchCreated(MatchCreated $event)
    {
        $user1 = $event->match->user1;
        $user2 = $event->match->user2;

        Redis::publish($this->allChannel, json_encode([
            'event'   => 'matchStarted',
            'userIds' => [$user1->id, $user2->id],
        ]));

        if ($user1->is_man) {
            Redis::publish($this->userChannelPrefix . $user1->id, json_encode([
                'event' => 'myMatchStarted',
                'user'  => $user2,
            ]));
        }

        if ($user2->is_man) {
            Redis::publish($this->userChannelPrefix . $user2->id, json_encode([
                'event' => 'myMatchStarted',
                'user'  => $user1,
            ]));
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Verified::class, static::class . '@onUserVerified');

        $events->listen(UserConnected::class, static::class . '@onUserConnected');

        $events->listen(UserDisconnected::class, static::class . '@onUserDisconnected');

        $events->listen(ChallengeCreated::class, static::class . '@onChallengeCreated');

        $events->listen(ChallengeRemoved::class, static::class . '@onChallengeRemoved');

        $events->listen(MatchCreated::class, static::class . '@onMatchCreated');
    }
}
