<?php
@ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/AuthManager.php';
require_once __DIR__ . '/includes/CMS.php';
require_once __DIR__ . '/includes/ThemeManager.php';

AuthManager::checkAuth();

header('Content-Type: application/json');

$themeManager = new ThemeManager();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$response = $themeManager->handleRequest($action);

echo json_encode($response);
exit;
