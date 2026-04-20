<?php
/**
 * Configuration for the portable Visual Editor
 */

// Simple security - change this!
define('ADMIN_PASSWORD', '1234');

// CMS Version information
require_once 'version.php';
define('REPO_URL', 'https://github.com/milanknez/fida-cms.git');

// The directory where HTML files reside (relative to this file)
define('ROOT_DIR', '../');

// Allowed extensions to edit
define('ALLOWED_EXTENSIONS', ['html', 'htm', 'php']);

// Session config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
