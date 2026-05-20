<?php

/**
 * CodeIgniter 4 Root Redirect
 *
 * This file serves as a bridge for environments where the document root
 * cannot be set to the 'public' directory.
 */

// Path to the front controller
$public_path = __DIR__ . '/public/index.php';

if (file_exists($public_path)) {
    require $public_path;
} else {
    echo "Error: public/index.php not found. Please ensure CodeIgniter 4 is installed correctly.";
}
