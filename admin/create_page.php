<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $filename = $data['filename'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['status' => 'error', 'message' => 'Název souboru nesmí být prázdný.']);
        exit;
    }

    if (strpos($filename, '.') === false) {
        $filename .= '.php';
    }

    if (!preg_match('/^[a-z0-9_-]+\.php$/i', $filename)) {
        echo json_encode(['status' => 'error', 'message' => 'Neplatný název souboru. Používejte jen písmena, čísla, pomlčky a podtržítka.']);
        exit;
    }

    $targetPath = realpath(ROOT_DIR) . '/' . $filename;
    
    if (file_exists($targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Soubor s tímto názvem již existuje.']);
        exit;
    }

    $template = "<?php\nrequire_once __DIR__ . '/admin/includes/CMS.php';\nCMS::getHeader();\n?>\n<section class=\"py-20 max-w-7xl mx-auto px-6 space-y-8\">\n    <h1 class=\"text-4xl font-extrabold text-white\">" . htmlspecialchars(ucfirst(str_replace('.php', '', $filename))) . "</h1>\n    <p class=\"text-slate-300\">Zde začněte tvořit obsah nové stránky...</p>\n</section>\n<?php\nCMS::getFooter();\n?>\n";

    if (file_put_contents($targetPath, $template)) {
        $pagesPath = ROOT_DIR . 'config/pages.json';
        $pages = [];
        if (file_exists($pagesPath)) {
            $pages = json_decode(file_get_contents($pagesPath), true);
        }
        
        $slug = str_replace('.php', '', $filename);
        $pages[$filename] = [
            'slug' => $slug,
            'title' => ucfirst($slug),
            'description' => '',
            'keywords' => ''
        ];
        file_put_contents($pagesPath, json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        require_once __DIR__ . '/includes/CMS.php';
        $gitResult = CMS::gitCommit("Create new page: $filename");

        echo json_encode([
            'status' => 'success', 
            'message' => "Stránka $filename byla vytvořena.",
            'redirect' => "index.php?page=$filename",
            'git_output' => $gitResult
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nepodařilo se vytvořit soubor.']);
    }
    exit;
}
