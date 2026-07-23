<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['files'])) {
        header('HTTP/1.1 400 Bad Request');
        exit('No files uploaded');
    }

    $uploadDir = ROOT_DIR . 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    $responses = [];
    $errors = [];

    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
        $name = $_FILES['files']['name'][$key];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "File type not allowed: $name";
            continue;
        }

        $cleanName = preg_replace("/[^a-z0-9\.]/i", "_", pathinfo($name, PATHINFO_FILENAME)) . '.' . $ext;
        $target = $uploadDir . $cleanName;
        
        if (move_uploaded_file($tmpName, $target)) {
            $responses[] = '../uploads/' . $cleanName;
        } else {
            $errors[] = "Failed to move uploaded file: $name";
        }
    }

    if (empty($responses) && !empty($errors)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => implode(', ', $errors)]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['data' => $responses]);
    }
    exit;
}
