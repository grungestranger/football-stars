<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use App\Models\User;

class ChallengeService
{
    /**
     * Create the challenge.
     *
     * @param User $user
     * @param User $opponent
     */
    public function createChallenge(User $user, User $opponent)
    {
        lockForUpdate(function () use ($user, $opponent) {
            if (
                $user->fromChallenges()->where('to_user_id', $opponent->id)->exists()
                || $user->toChallenges()->where('from_user_id', $opponent->id)->exists()
            ) {
                throw ValidationException::withMessages([trans('common.challenge_already_exists')]);
            }

            $user->fromchallenges()->create(['to_user_id' => $opponent->id]);
        }, $user, $opponent);
    }

    /**
     * Remove the challenge.
     *
     * @param User $user
     * @param User $opponent
     */
    public function removeChallenge(User $user, User $opponent)
    {
        lockForUpdate(function () use ($user, $opponent) {
            if ($user->fromChallenges()->where('to_user_id', $opponent->id)->exists()) {
                $user->fromChallenges()->where('to_user_id', $opponent->id)->delete();
            } elseif ($user->toChallenges()->where('from_user_id', $opponent->id)->exists()) {
                $user->toChallenges()->where('from_user_id', $opponent->id)->delete();
            } else {
                throw ValidationException::withMessages([trans('common.challenge_is_not_found')]);
            }
        }, $user, $opponent);
    }
}
