<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Events\ChallengeCreated;
use App\Events\ChallengeRemoved;
use App\Events\MatchCreated;
use App\Services\ChallengeService;
use App\Services\MatchService;
use App\Services\UserService;

class AppController extends Controller
{
    /**
     * Get user list.
     *
     * @param UserService $userService
     * @return JsonResponse
     */
    public function getUsers(UserService $userService): JsonResponse
    {
        $authUser = auth()->user();
        $users    = [];

        foreach ($userService->getList() as $user) {
            $canGetChallenge = $user->id != $authUser->id
                && $authUser->fromChallenges->where('to_user_id', $user->id)->isEmpty()
                && $authUser->toChallenges->where('from_user_id', $user->id)->isEmpty();

            $user = $user->toArray();
            $user['can_get_challenge'] = $canGetChallenge;

            $users[] = $user;
        }

        return response()->json(['users' => $users]);
    }

    /**
     * Get jwt.
     *
     * @return JsonResponse
     */
    public function jwt(): JsonResponse
    {
        return response()->json(['token' => JWTAuth::fromUser(auth()->user())]);
    }

    /**
     * Get common data.
     *
     * @return JsonResponse
     */
    public function getCommonData(): JsonResponse
    {
        $authUser = auth()->user();

        return response()->json([
            'fromChallenges' => $authUser->fromChallenges->where('toUser', '!=', null),
            'toChallenges'   => $authUser->toChallenges->where('fromUser', '!=', null),
        ]);
    }

    /**
     * Create the challenge.
     *
     * @param ChallengeService $challengeService
     * @param Request $request
     */
    public function createChallenge(Request $request, ChallengeService $challengeService)
    {
        $user     = auth()->user();
        $opponent = $this->getOpponent($user, $request);

        $challengeService->createChallenge($user, $opponent);

        event(new ChallengeCreated($user, $opponent));
    }

    /**
     * Remove the challenge.
     *
     * @param ChallengeService $challengeService
     * @param Request $request
     */
    public function removeChallenge(Request $request, ChallengeService $challengeService)
    {
        $user     = auth()->user();
        $opponent = $this->getOpponent($user, $request);

        $challengeService->removeChallenge($user, $opponent);

        event(new ChallengeRemoved($user, $opponent));
    }

    /**
     * Get the opponent.
     *
     * @param User $user
     * @param Request $request
     * @return User
     * @throws ValidationException
     */
    protected function getOpponent(User $user, Request $request): User
    {
        $opponentId = (int) $request->input('user_id');

        if (!$opponentId) {
            throw ValidationException::withMessages([trans('common.wrong_data')]);
        }

        if ($opponentId == $user->id) {
            throw ValidationException::withMessages([trans('common.challenge_for_yourself_error')]);
        }

        $opponent = User::find($opponentId);

        if (!$opponent) {
            throw ValidationException::withMessages([trans('common.user_is_not_found')]);
        }

        return $opponent;
    }

    /**
     * Start the match.
     *
     * @param Request $request
     * @param MatchService $matchService
     */
    public function play(Request $request, MatchService $matchService)
    {
        $user     = auth()->user();
        $opponent = $this->getOpponent($user, $request);

        $match = $matchService->createMatch($user, $opponent);

        event(new MatchCreated($match));
    }
}
