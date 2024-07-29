<?php

namespace App\Helpers;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminHelper
{
    /**
     * Check if a user with the given ID exists.
     *
     * @param int $admin_id
     * @return array
     */
    public static function checkAdminFound($admin_id)
    {
        $admin = Admin::find($admin_id);

        if (!$admin) {
            return ['status' => 404, 'message' => 'Not Found', 'data' => []];
        }

        return ['status' => 200, 'message' => 'Admin Found', 'data' => $admin];
    }
    /**
     * Validate if the given user ID is valid.
     *
     * @param mixed $admin_id
     * @return bool
     */

    private static function isValidAdminId($admin_id)
    {
        return is_numeric($admin_id) && (int)$admin_id > 0;
    }

    /**
     * Authorize if the given user ID matches the currently authenticated user's ID.
     *
     * @param int $admin_id
     * @return bool
     */

    public static function authorizeAdmin($admin_id)
    {
        if (!self::isValidAdminId($admin_id)) {
            return false;
        }

        $currentAdminId = Auth::guard('admin')->user()->id;
        return $currentAdminId === (int) $admin_id;
    }
    /**
     * Check if the current password matches the provided password for a user.
     *
     * @param \App\Models\User $user
     * @param string $current_password
     * @return bool
     */

    public static function checkCurrentPassword($admin, $current_password)
    {
        return Hash::check($current_password, $admin->password);
    }
}
