<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JWTAuth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Events\ChallengeCreated;
use App\Events\ChallengeRemoved;

class AppController extends Controller
{
    /**
     * Get user list.
     *
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        $authUser = auth()->user();
        $users    = [];

        foreach (User::getList() as $user) {
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
     * Remove the challenge.
     *
     * @param Request $request
     */
    public function removeChallenge(Request $request)
    {
        $user     = auth()->user();
        $opponent = $this->getOpponent($user, $request);

        $user->removeChallenge($opponent);

        event(new ChallengeRemoved($user, $opponent));
    }

    /**
     * Create a challenge.
     *
     * @param Request $request
     */
    public function createChallenge(Request $request)
    {
        $user     = auth()->user();
        $opponent = $this->getOpponent($user, $request);

        $user->createChallenge($opponent);

        event(new ChallengeCreated($user, $opponent));
    }

    /**
     *
     *
     * @param User $user
     * @param Request $request
     * @return User
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
}
