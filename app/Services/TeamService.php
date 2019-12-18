<?php

namespace App\Services;

use App\Models\PlayerSchema;
use App\Models\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class TeamService
{
    /**
     * Create default schema and players.
     *
     * @param User $user
     */
    public function createTeam(User $user)
    {
        lockForUpdate(function () use ($user) {
            if ($user->schemas()->first()) {
                throw ValidationException::withMessages([trans('common.team_already_exists')]);
            }

            $schema = $user->schemas()->create([
                'name'     => config('schema.default_name'),
                'settings' => config('schema.default_settings'),
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

                    foreach ($val['data_range'] as $key1 => $val1) {
                        $data[$key1] = rand($val1[0], $val1[1]);
                    }

                    $player = $user->players()->create($data);

                    // Roles.

                    $data = [
                        [
                            'player_id' => $player->id,
                            'role_id'   => $roles[$key],
                        ],
                    ];

                    $addRolesCount = rand(0, config('player.additional_roles_max_count'));
                    $addRoles      = $val['additional_roles'];

                    while ($addRolesCount && count($addRoles)) {
                        $addRoleKey = array_rand($addRoles);

                        $data[] = [
                            'player_id' => $player->id,
                            'role_id'   => $roles[$addRoles[$addRoleKey]],
                        ];

                        unset($addRoles[$addRoleKey]);
                        $addRolesCount--;
                    }

                    $player->playerRoles()->insert($data);

                    // Schemas.

                    if (isset($val['default_position']) && $defaultPosCount < count($val['default_position'])) {
                        $defSet = [
                            'position'     => $val['default_position'][$defaultPosCount],
                            'reserve_index' => NULL,
                        ];
                        $defaultPosCount++;
                    } else {
                        $defSet = [
                            'position'     => NULL,
                            'reserve_index' => $reserveIndex,
                        ];
                        $reserveIndex++;
                    }

                    $player->playerSchemas()->create([
                        'schema_id' => $schema->id,
                        'settings'  => $defSet,
                    ]);
                }
            }
        }, $user);
    }

    /**
     * Get user's team.
     *
     * @param User $user
     * @return Collection
     */
    public function getTeam(User $user): Collection
    {
        $players = $user->players()->with([
            'playerSchemas' => function ($query) use ($user) {
                $query->where('schema_id', $user->current_schema->id);
            },
            'playerRoles',
        ])->get();

        foreach ($players as $player) {
            $player->settings = $player->playerSchemas->first()->settings;
            $player->roles    = collect(config('player.roles'))->only($player->playerRoles->pluck('role_id'));

            unset($player->playerSchemas, $player->playerRoles);
        }

        return $this->sortPlayers($players);
    }

    /**
     *
     *
     * @param Schema $schema
     * @return Collection
     */
    public function getPlayersBySchema(Schema $schema): Collection
    {
        $players = $schema->playerSchemas()->select('player_id AS id', 'settings')->get();

        return $this->sortPlayers($players);
    }

    /**
     * Sort players by position.
     *
     * @param Collection $players
     * @return Collection
     */
    protected function sortPlayers(Collection $players): Collection
    {
        $result = $temp = [];

        foreach ($players as $item) {
            if ($item->settings->position) {
                foreach (array_values(config('player.role_areas')) as $k => $v) {
                    if (
                        $item->settings->position->x >= $v['x'][0]
                        && $item->settings->position->x <= $v['x'][1]
                        && $item->settings->position->y >= $v['y'][0]
                        && $item->settings->position->y <= $v['y'][1]
                    ) {
                        $temp[$k][] = $item;
                        break;
                    }
                }
            } else {
                $result[config('player.on_field_count') + $item->settings->reserve_index] = $item;
            }
        }

        foreach ($temp as &$item) {
            if (count($item) > 1) {
                usort($item, function ($a, $b) {
                    if ($a->settings->position->y < $b->settings->position->y) {
                        return 1;
                    } elseif ($a->settings->position->y > $b->settings->position->y) {
                        return -1;
                    } else {
                        if ($a->settings->position->x < $b->settings->position->x) {
                            return -1;
                        } elseif ($a->settings->position->x > $b->settings->position->x) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                });
            }
        }
        unset($item);

        ksort($temp);

        $i = 0;
        foreach ($temp as $item) {
            foreach ($item as $item1) {
                $result[$i] = $item1;
                $i++;
            }
        }

        ksort($result);

        return collect($result);
    }

    /**
     * Get prepared to save player settings.
     *
     * @param array $playerSettings
     * @return array
     */
    protected function getPreparedToSavePlayerSettings(array $playerSettings): array
    {
        foreach ($playerSettings as &$settings) {
            if ($settings['reserve_index'] == 'NULL') {
                $settings['reserve_index'] = NULL;
                $settings['position']      = json_decode($settings['position']);
            } else {
                $settings['reserve_index'] = (int) $settings['reserve_index'];
                $settings['position']      = NULL;
            }

            $settings = json_encode($settings);
        }
        unset($settings);

        return $playerSettings;
    }

    /**
     * @param array $data
     */
    protected function validatePlayerSettings(array $data)
    {
        // todo
    }

    /**
     * Validate schema's settings.
     *
     * @param array $data
     * @throws ValidationException
     */
    protected function validateSchemaSettings(array $data)
    {
        foreach (config('schema.options') as $option => $settings) {
            if (!isset($data[$option]) || !in_array($data[$option], $settings)) {
                throw ValidationException::withMessages([trans('common.wrong_data')]);
            }
        }
    }

    /**
     * Save the schema and players' settings.
     *
     * @param User $user
     * @param int $schemaId
     * @param array $schemaSettings
     * @param array $playerSettings
     * @throws ValidationException
     */
    public function saveSchema(User $user, int $schemaId, array $schemaSettings, array $playerSettings)
    {
        $schema = $user->schemas()->find($schemaId);

        if (!$schema) {
            throw ValidationException::withMessages([trans('team.schema_not_exist')]);
        }

        $this->validateSchemaSettings($schemaSettings);
        $this->validatePlayerSettings($playerSettings);

        $playerSettings = $this->getPreparedToSavePlayerSettings($playerSettings);

        DB::transaction(function () use ($user, $schema, $schemaSettings, $playerSettings) {
            $schema->update(['settings' => $schemaSettings]);

            foreach ($playerSettings as $playerId => $settings) {
                PlayerSchema::where([
                    ['schema_id', $schema->id],
                    ['player_id', $playerId],
                ])->update(['settings' => $settings]);
            }
        });
    }

    /**
     * Create a scheme and its corresponding players' settings.
     *
     * @param User $user
     * @param string $schemaName
     * @param array $schemaSettings
     * @param array $playerSettings
     * @return Schema
     * @throws ValidationException
     */
    public function createSchema(User $user, string $schemaName, array $schemaSettings, array $playerSettings): Schema
    {
        if ($user->schemas()->where('name', $schemaName)->exists()) {
            throw ValidationException::withMessages([trans('team.duplicate_schema_name')]);
        }

        $this->validateSchemaSettings($schemaSettings);
        $this->validatePlayerSettings($playerSettings);

        $playerSettings = $this->getPreparedToSavePlayerSettings($playerSettings);

        DB::transaction(function () use ($user, $schemaName, $schemaSettings, $playerSettings, &$schema) {
            $schema = $user->schemas()->create([
                'name'     => $schemaName,
                'settings' => $schemaSettings,
            ]);

            $set = [];

            foreach ($playerSettings as $playerId => $settings) {
                $set[] = [
                    'schema_id' => $schema->id,
                    'player_id' => $playerId,
                    'settings'  => $settings,
                ];
            }

            PlayerSchema::insert($set);
        });

        return $schema;
    }

    /**
     * Remove user's schema.
     *
     * @param User $user
     * @param int $schemaId
     * @throws ValidationException
     */
    public function removeSchema(User $user, int $schemaId)
    {
        $schema = $user->schemas()->find($schemaId);

        if (!$schema) {
            throw ValidationException::withMessages([trans('team.schema_not_exist')]);
        }

        lockForUpdate(function () use ($user, $schema) {
            if ($user->schemas()->count() < 2) {
                throw ValidationException::withMessages([trans('team.remove_last_schema')]);
            }

            $schema->delete();
        }, $user);
    }
}
