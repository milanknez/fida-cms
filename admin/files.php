<?php
@ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CMS.php';
require_once __DIR__ . '/includes/FileManager.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$fileManager = new FileManager();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$response = $fileManager->handleRequest($action);

echo json_encode($response);
exit;
