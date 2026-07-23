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

    $template = "<?php 
require_once 'includes/CMS.php';
\$meta = CMS::getPageMeta();
?>
<!DOCTYPE html>
<html lang=\"cs\">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class=\"section-padding\">
            <div class=\"container\">
                <h1>Nová stránka: " . str_replace('.php', '', $filename) . "</h1>
                <p>Zde začněte tvořit svůj obsah...</p>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>";

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

        require_once ROOT_DIR . 'includes/CMS.php';
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
