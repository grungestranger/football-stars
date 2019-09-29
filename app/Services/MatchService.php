<?php

namespace App\Services;

use App\Models\Match;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class MatchService
{
    /**
     * Create the match.
     *
     * @param User $user
     * @param User $opponent
     * @return Match
     */
    public function createMatch(User $user, User $opponent): Match
    {
        lockForUpdate(function () use ($user, $opponent, &$match) {
            if (!$user->online || !$opponent->online) {
                throw ValidationException::withMessages([trans('common.players_must_be_online')]);
            }

            if ($user->is_match || $opponent->is_match) {
                throw ValidationException::withMessages([trans('common.players_playing')]);
            }

            if (!$user->toChallenges()->where('from_user_id', $opponent->id)->exists()) {
                throw ValidationException::withMessages([trans('common.challenge_is_not_found')]);
            }

            $user->toChallenges()->where('from_user_id', $opponent->id)->delete();

            $match = $user->match1()->create(['user2_id' => $opponent->id]);
        }, $user, $opponent);

        return $match;
    }
}
