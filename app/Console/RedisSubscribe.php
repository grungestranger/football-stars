<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Redis;
use App\Models\User;
use App\Events\UserConnected;
use App\Events\UserDisconnected;

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
     */
    public function handle()
    {
        $redis = Redis::connection('subscribe');

        $redis->subscribe('system', function ($message) {
            $data = json_decode($message);

            if ($data && isset($data->event)) {
                switch ($data->event) {
                    case 'serverRestarted':
                        User::resetOnline();
                        break;
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
