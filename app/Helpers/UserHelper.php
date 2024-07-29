<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserHelper
{
    /**
     * Check if a user with the given ID exists.
     *
     * @param int $user_id
     * @return array
     */
    public static function checkUserFound($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return ['status' => 404, 'message' => 'Not Found', 'data' => []];
        }

        return ['status' => 200, 'message' => 'User Found', 'data' => $user];
    }

    /**
     * Validate if the given user ID is valid.
     *
     * @param mixed $user_id
     * @return bool
     */
    private static function isValidUserId($user_id)
    {
        return is_numeric($user_id) && (int)$user_id > 0;
    }

    /**
     * Authorize if the given user ID matches the currently authenticated user's ID.
     *
     * @param int $user_id
     * @return bool
     */
    public static function authorizeUser($user_id)
    {
        if (!self::isValidUserId($user_id)) {
            return false;
        }

        $currentUserId = Auth::user()->id;
        return $currentUserId === (int) $user_id;
    }

    /**
     * Check if the current password matches the provided password for a user.
     *
     * @param \App\Models\User $user
     * @param string $current_password
     * @return bool
     */
    public static function checkCurrentPassword($user, $current_password)
    {
        return Hash::check($current_password, $user->password);
    }
}
