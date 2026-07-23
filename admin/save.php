<?php
@ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/AuthManager.php';
require_once __DIR__ . '/includes/CMS.php';
require_once __DIR__ . '/includes/PageManager.php';

AuthManager::checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['html'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['status' => 'error', 'message' => 'Neplatná data']);
        exit;
    }

    $page = $data['page'] ?? $_SESSION['current_page'] ?? ($data['metadata']['slug'] ?? 'index.php');
    if (!preg_match('/\.(php|html)$/i', $page)) {
        $page .= '.php';
    }

    $pageManager = new PageManager();
    $result = $pageManager->savePage($page, $data['html'], $data['metadata'] ?? null);

    if (isset($result['status']) && $result['status'] === 'error') {
        http_response_code(500);
    }

    echo json_encode($result);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Metoda není podporována']);
exit;
