<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
//use DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, TableName;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the relation to schemas.
     *
     * @return HasMany
     */
    public function schemas(): HasMany
    {
        return $this->hasMany(UserSchema::class)->orderBy('id');
    }

    /**
     * Get the relation to players.
     *
     * @return HasMany
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Create default schema and players.
     *
     * @throws \Exception
     */
    public function createTeam()
    {
        lockTables(
            [
                [UserSchema::getTableName(), 'w'],
                [Player::getTableName(), 'w'],
                [PlayerRoles::getTableName(), 'w'],
                [PlayerSettings::getTableName(), 'w'],
            ],
            function () {
                if ($this->schemas()->first()) {
                    throw new \Exception("Team already exists.");
                }

                $schema = $this->schemas()->create([
                    'name'     => config('schema.defaultName'),
                    'settings' => json_encode(config('schema.defaultSettings')),
                ]);

                $roles    = array_flip(config('player.roles'));
                $names    = config('player.names');
                $surnames = config('player.surnames');

                $reserveIndex = 0;

                foreach (config('player.roles_data') as $key => $val) {
                    $defaultPosCount = 0;

                    for ($i = 0; $i < $val['count']; $i++) {
                        // Player.

                        $data = [
                            'name' => $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)],
                        ];

                        foreach ($val['dataRange'] as $key1 => $val1) {
                            $data[$key1] = rand($val1[0], $val1[1]);
                        }

                        $player = $this->players()->create($data);

                        // Roles.

                        $data = [
                            [
                                'player_id' => $player->id,
                                'role_id'   => $roles[$key],
                            ],
                        ];

                        $addRolesCount = rand(0, config('player.add_roles_max_count'));
                        $addRoles      = $val['addRoles'];

                        while ($addRolesCount && count($addRoles)) {
                            $addRoleKey = array_rand($addRoles);

                            $data[] = [
                                'player_id' => $player->id,
                                'role_id'   => $roles[$addRoles[$addRoleKey]],
                            ];

                            unset($addRoles[$addRoleKey]);
                            $addRolesCount--;
                        }

                        $player->roles()->insert($data);

                        // Settings.

                        if (isset($val['defaultPos']) && $defaultPosCount < count($val['defaultPos'])) {
                            $defSet = [
                                'position'     => $val['defaultPos'][$defaultPosCount],
                                'reserveIndex' => NULL,
                            ];
                            $defaultPosCount++;
                        } else {
                            $defSet = [
                                'position'     => NULL,
                                'reserveIndex' => $reserveIndex,
                            ];
                            $reserveIndex++;
                        }

                        $player->settings()->create([
                            'schema_id' => $schema->id,
                            'settings'  => json_encode($defSet),
                        ]);
                    }
                }
            }
        );
    }
}
