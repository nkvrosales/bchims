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
