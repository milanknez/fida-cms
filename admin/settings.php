<?php
@ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/AuthManager.php';
require_once __DIR__ . '/includes/CMS.php';
require_once __DIR__ . '/includes/SettingsManager.php';

AuthManager::checkAuth();

header('Content-Type: application/json');

$settingsManager = new SettingsManager();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'rebuild_cache') {
        echo json_encode($settingsManager->rebuildCache());
        exit;
    }
    if ($_GET['action'] === 'fix_permissions') {
        echo json_encode($settingsManager->fixPermissions());
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['favicon_file']) || (isset($_GET['action']) && $_GET['action'] === 'upload_favicon')) {
        if (!isset($_FILES['favicon_file'])) {
            echo json_encode(['status' => 'error', 'message' => 'Nebyl nahrán žádný soubor.']);
            exit;
        }
        echo json_encode($settingsManager->uploadFavicon($_FILES['favicon_file']));
        exit;
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Neplatná data']);
        exit;
    }

    echo json_encode($settingsManager->updateSettings($data));
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Metoda není podporována']);
exit;
