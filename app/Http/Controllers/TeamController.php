<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Services\TeamService;

class TeamController extends Controller
{
    /**
     * Show team controls dashboard.
     *
     * @param TeamService $teamService
     * @return View
     */
    public function index(TeamService $teamService): View
    {
        $user = auth()->user();

        $data = [
            'currentSchema' => $user->current_schema,
            'players'       => $teamService->getTeam($user),
            'schemas'       => $user->schemas,
        ];

        return view('team', $data);
    }

    /**
     * Save the schema.
     *
     * @param Request $request
     * @param TeamService $teamService
     * @throws ValidationException
     */
    public function saveSchema(Request $request, TeamService $teamService)
    {
        $user = auth()->user();

        $schema         = $request->input('schema');
        $playerSettings = $request->input('player_settings');

        $this->validateInputData($schema, $playerSettings);

        if (!isset($schema['id']) || !is_string($schema['id'])) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        if (!$user->schemas()->where('id', $schema['id'])->exists()) {
            throw ValidationException::withMessages([trans('team.schema_not_exist')]);
        }

        $teamService->saveSchema($user, $schema, $playerSettings);
    }

    /**
     * Create a schema.
     *
     * @param Request $request
     * @param TeamService $teamService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function createSchema(Request $request, TeamService $teamService): JsonResponse
    {
        $user = auth()->user();

        $schema         = $request->input('schema');
        $playerSettings = $request->input('player_settings');

        $this->validateInputData($schema, $playerSettings);

        if (!isset($schema['name']) || !is_string($schema['name'])) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        if ($user->schemas()->where('name', $schema['name'])->exists()) {
            throw ValidationException::withMessages([trans('team.duplicate_schema_name')]);
        }

        $schema = $teamService->createSchema($user, $schema, $playerSettings);

        unset($schema->settings);

        return response()->json(['schema' => $schema]);
    }

    /**
     * Validate input data.
     *
     * @param $schema
     * @param $playerSettings
     * @throws ValidationException
     */
    protected function validateInputData($schema, $playerSettings)
    {
        if (
            !is_array($schema)
            || !isset($schema['settings'])
            || !is_array($schema['settings'])
            || !is_array($playerSettings)
        ) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }
    }
}
