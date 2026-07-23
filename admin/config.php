<?php
/**
 * Configuration for Fida CMS Visual Editor
 */

// Admin password - change for each project!
define('ADMIN_PASSWORD', 'admin123');

// CMS Repository for Updates
require_once 'version.php';
define('REPO_URL', 'https://github.com/milanknez/fida-cms.git');
define('GITHUB_TOKEN', '');

// Directory where web PHP/HTML files reside (relative to admin directory)
define('ROOT_DIR', '../');

// Allowed extensions to edit
define('ALLOWED_EXTENSIONS', ['html', 'htm', 'php']);

// Session config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
