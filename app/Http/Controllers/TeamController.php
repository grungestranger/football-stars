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
     * @var TeamService
     */
    protected $teamService;

    /**
     * Constructor.
     *
     * @param TeamService $teamService
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Show team controls dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();

        $data = [
            'currentSchema' => $user->current_schema,
            'players'       => $this->teamService->getTeam($user),
            'schemas'       => $user->schemas,
        ];

        return view('team', $data);
    }

    public function getSchema($id): JsonResponse
    {
        $user = auth()->user();

        $schema = $user->schemas()->find((int) $id);

        if (!$schema) {
            throw ValidationException::withMessages([trans('team.schema_not_exist')]);
        }

        $user->setLastSchemaId($schema->id);

        return response()->json([
            'schema'  => $schema,
            'players' => $this->teamService->getPlayersBySchema($schema),
        ]);
    }

    /**
     * Save the schema.
     *
     * @param Request $request
     * @throws ValidationException
     */
    public function saveSchema(Request $request)
    {
        $user = auth()->user();

        $schema         = $request->input('schema');
        $playerSettings = $request->input('player_settings');

        $this->validateInputData($schema, $playerSettings);

        if (!isset($schema['id']) || !is_string($schema['id'])) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        $this->teamService->saveSchema($user, (int) $schema['id'], $schema['settings'], $playerSettings);
    }

    /**
     * Create a schema.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function createSchema(Request $request): JsonResponse
    {
        $user = auth()->user();

        $schema         = $request->input('schema');
        $playerSettings = $request->input('player_settings');

        $this->validateInputData($schema, $playerSettings);

        if (!isset($schema['name']) || !is_string($schema['name'])) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        $schema = $this->teamService->createSchema($user, $schema['name'], $schema['settings'], $playerSettings);

        $user->setLastSchemaId($schema->id);

        unset($schema->settings);

        return response()->json(['schema' => $schema]);
    }

    /**
     * Remove the schema.
     *
     * @param Request $request
     * @throws ValidationException
     */
    public function removeSchema(Request $request)
    {
        $user     = auth()->user();
        $schemaId = (int) $request->input('id');

        if (!$schemaId) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        $this->teamService->removeSchema($user, $schemaId);
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
