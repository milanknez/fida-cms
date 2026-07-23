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
    $metaData = $data['metadata'] ?? null;
    
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
        $oldContent = file_get_contents($targetPath);
        
        // Extract PHP header (before first section or HTML tag) and footer if present
        $headerPhp = "";
        $footerPhp = "";
        
        if (preg_match('/^(<\?php[\s\S]*?\?>)/i', $oldContent, $matches)) {
            $headerPhp = $matches[1] . "\n\n";
        }
        if (preg_match('/(<\?php\s+include\s+[\'"]partials\/footer\.php[\'"];\s*\?>)$/i', $oldContent, $matches)) {
            $footerPhp = "\n\n" . $matches[1];
        }
        
        if (!empty($headerPhp)) {
            $html = preg_replace('/^[\s\S]*?<body[^>]*>/i', '', $html);
            $html = preg_replace('/<\/body>[\s\S]*$/i', '', $html);
            $html = $headerPhp . trim($html) . ($footerPhp ?: "\n\n<?php include 'partials/footer.php'; ?>");
        }
    }

    // Save HTML
    if (file_put_contents($targetPath, $html)) {
        // Save Metadata if provided
        if ($metaData) {
            $pagesPath = ROOT_DIR . 'config/pages.json';
            $pages = [];
            if (file_exists($pagesPath)) {
                $pages = json_decode(file_get_contents($pagesPath), true);
            }
            $pages[$currentPage] = [
                'slug' => $metaData['slug'] ?? '',
                'title' => $metaData['title'] ?? '',
                'description' => $metaData['description'] ?? '',
                'keywords' => $metaData['keywords'] ?? ''
            ];
            file_put_contents($pagesPath, json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // Auto-commit
        require_once ROOT_DIR . 'includes/CMS.php';
        CMS::generateCache();
        $gitMsg = "Auto-update: $currentPage";
        $gitResult = CMS::gitCommit($gitMsg);
        
        $status = (strpos($gitResult, 'ERROR:') === 0) ? 'warning' : 'success';
        $msg = ($status === 'warning') ? "Stránka byla uložena lokálně." : "Stránka $currentPage byla uložena a commitnuta.";

        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status, 
            'message' => $msg,
            'git_output' => $gitResult
        ]);

    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Chyba při zápisu do souboru.']);
    }
    exit;
}
