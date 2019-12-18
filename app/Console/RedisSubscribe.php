<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use App\Events\UserConnected;
use App\Events\UserDisconnected;
use App\Services\UserService;

class RedisSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * Execute the console command.
     *
     * @param UserService $userService
     */
    public function handle(UserService $userService)
    {
        $userService->resetOnline();

        $redis = Redis::connection('subscribe');

        $redis->subscribe(env('SYSTEM_CHANNEL'), function ($message) use ($userService) {
            $data = json_decode($message);

            if ($data && isset($data->event)) {
                switch ($data->event) {
                    case 'userConnected':
                        if (isset($data->userId) && is_int($data->userId)) {
                            $user = User::find($data->userId);

                            if ($user) {
                                $user->setOnline();

                                event(new UserConnected($user));
                            }
                        }

                        break;
                    case 'userDisconnected':
                        if (isset($data->userId) && is_int($data->userId)) {
                            $user = User::find($data->userId);

                            if ($user) {
                                $user->unsetOnline();

                                event(new UserDisconnected($user));
                            }
                        }

                        break;
                }
            }
        });
    }
}
