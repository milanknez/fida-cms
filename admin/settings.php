<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'rebuild_cache') {
        require_once ROOT_DIR . 'includes/CMS.php';
        CMS::generateCache();
        
        CMS::gitCommit("Manual cache regeneration");
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Cache byla úspěšně přegenerována.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid data');
    }

    $sitePath = ROOT_DIR . 'config/site.json';
    $site = [];
    if (file_exists($sitePath)) {
        $site = json_decode(file_get_contents($sitePath), true);
    }

    if (isset($data['site_name'])) $site['site_name'] = $data['site_name'];
    if (isset($data['phone_nonstop'])) $site['phone_nonstop'] = $data['phone_nonstop'];
    if (isset($data['phone_landline'])) $site['phone_landline'] = $data['phone_landline'];
    if (isset($data['email'])) $site['email'] = $data['email'];
    if (isset($data['address_headquarters'])) $site['address_headquarters'] = $data['address_headquarters'];
    if (isset($data['address_dispatch'])) $site['address_dispatch'] = $data['address_dispatch'];
    if (isset($data['ga_id'])) $site['ga_id'] = $data['ga_id'];
    if (isset($data['contact_form_recipient'])) $site['contact_form_recipient'] = $data['contact_form_recipient'];
    if (isset($data['error_page_404'])) $site['error_page_404'] = $data['error_page_404'];
    $site['enable_cache'] = !empty($data['enable_cache']) ? true : false;
    
    if (file_put_contents($sitePath, json_encode($site, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        require_once ROOT_DIR . 'includes/CMS.php';
        CMS::generateCache();
        CMS::gitCommit("Update global site settings");
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Globální nastavení bylo uloženo a cache aktualizována.']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Chyba při zápisu do site.json.']);
    }
    exit;
}
