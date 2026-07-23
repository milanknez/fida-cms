<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CMS.php';
require_once __DIR__ . '/includes/PageManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$pageManager = new PageManager(ROOT_DIR);
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$action = $_GET['action'] ?? $data['action'] ?? '';

if ($action === 'create') {
    $title = trim($data['title'] ?? '');
    $slug = trim($data['slug'] ?? '');
    
    if (empty($title)) {
        echo json_encode(['status' => 'error', 'message' => 'Název stránky nesmí být prázdný.']);
        exit;
    }

    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $title)));
        $slug = trim($slug, '-');
    }

    $fileName = $slug . '.php';
    if (file_exists(ROOT_DIR . $fileName)) {
        echo json_encode(['status' => 'error', 'message' => "Stránka $fileName již existuje."]);
        exit;
    }

    $defaultTemplate = "<?php\nrequire_once __DIR__ . '/admin/includes/CMS.php';\nCMS::getHeader();\n?>\n<section class=\"py-20 max-w-7xl mx-auto px-6 space-y-8\">\n    <h1 class=\"text-4xl font-extrabold text-white\">" . htmlspecialchars($title) . "</h1>\n    <p class=\"text-slate-300\">Obsah stránky upravíte v administraci Fida CMS.</p>\n</section>\n<?php\nCMS::getFooter();\n?>\n";

    $res = $pageManager->savePage($fileName, $defaultTemplate, [
        'slug' => $slug,
        'title' => $title,
        'description' => '',
        'keywords' => ''
    ]);

    echo json_encode($res);
    exit;
}

if ($action === 'delete') {
    $fileName = trim($data['filename'] ?? '');
    if (empty($fileName)) {
        echo json_encode(['status' => 'error', 'message' => 'Není zadán název souboru.']);
        exit;
    }
    
    $res = $pageManager->deletePage($fileName);
    echo json_encode($res);
    exit;
}

if ($action === 'update_meta') {
    $fileName = trim($data['filename'] ?? '');
    if (empty($fileName)) {
        echo json_encode(['status' => 'error', 'message' => 'Není zadán název souboru.']);
        exit;
    }

    $res = $pageManager->updatePageMetadata($fileName, [
        'slug' => $data['slug'] ?? '',
        'title' => $data['title'] ?? '',
        'description' => $data['description'] ?? '',
        'keywords' => $data['keywords'] ?? ''
    ]);

    if ($res) {
        echo json_encode(['status' => 'success', 'message' => 'Metadata stránky byla aktualizována.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Chyba při ukládání metadat.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Neznámá akce.']);
