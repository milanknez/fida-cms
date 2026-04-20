<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['html'])) {
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid data');
    }

    $html = $data['html'];
    
    // Determine which file to save
    $currentPage = $_SESSION['current_page'] ?? 'index.html';
    $targetPath = realpath(ROOT_DIR . $currentPage);
    $basePath = realpath(ROOT_DIR);

    // Safety: Ensure we stay within root
    if (strpos($targetPath, $basePath) !== 0) {
        header('HTTP/1.1 403 Forbidden');
        exit('Invalid path');
    }

    // Backup
    if (file_exists($targetPath)) {
        copy($targetPath, $targetPath . '.bak');
    }

    // Save
    if (file_put_contents($targetPath, $html)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => "Stránka $currentPage byla uložena."]);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Chyba při zápisu do souboru.']);
    }
    exit;
}
