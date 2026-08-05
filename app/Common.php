<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

/**
 * Returns true if the given (or currently logged-in) user has admin-level
 * privileges. Both 'admin' and 'dev' roles are treated as administrators
 * throughout the application.
 */
if (!function_exists('is_admin_role')) {
    function is_admin_role(?string $role = null): bool
    {
        $r = $role ?? session()->get('role');
        return in_array(strtolower((string)$r), ['admin', 'dev'], true);
    }
}

/**
 * Enforces single-session login. Returns true if the current session's
 * stored session_token matches the token currently held by the user in the
 * database. When a user logs in on another device, a fresh token is issued,
 * so the older session's token no longer matches and this returns false —
 * which callers treat as a terminated session.
 *
 * @param array|null $user Optionally pass an already-fetched user row to avoid a duplicate query.
 */
if (!function_exists('validate_session_token')) {
    function validate_session_token(?array $user = null): bool
    {
        $sessionToken = (string) session()->get('session_token');
        if ($sessionToken === '') {
            return false;
        }
        if ($user === null) {
            $user = (new \App\Models\UserModel())->get_user_by_id(session()->get('user_id'));
        }
        return !empty($user) && !empty($user['session_token']) && hash_equals((string) $user['session_token'], $sessionToken);
    }
}
