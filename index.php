<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 1. UI Language Handling
$uiLang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'cs';

// 2. Scan for editable files
$files = glob(ROOT_DIR . "*.{html,htm,php}", GLOB_BRACE);
$editableFiles = [];
$pageTitles = [];

foreach ($files as $file) {
    $basename = basename($file);
    // Skip admin files and specific scripts
    if (!in_array($basename, ['login.php', 'index.php', 'save.php', 'config.php', 'sw.js'])) {
        $editableFiles[] = $basename;
        
        // Extract title
        $title = "";
        $fileContent = @file_get_contents($file);
        if ($fileContent && preg_match('/<title>(.*?)<\/title>/is', $fileContent, $matches)) {
            $title = trim($matches[1]);
        }
        $pageTitles[$basename] = $title ?: $basename;
    }
}

// 3. Identify current file
$currentPage = isset($_GET['page']) && in_array($_GET['page'], $editableFiles) ? $_GET['page'] : 'index.html';
if (!in_array($currentPage, $editableFiles) && !empty($editableFiles)) {
    $currentPage = $editableFiles[0];
}

$targetPath = ROOT_DIR . $currentPage;
$content = file_exists($targetPath) ? file_get_contents($targetPath) : '';
$_SESSION['current_page'] = $currentPage;

?>
<!DOCTYPE html>
<html lang="<?= $uiLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web editor | <?= $currentPage ?></title>
    
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            /* Modern Slate & Indigo Palette */
            --primary: #4f46e5; /* Indigo */
            --primary-dark: #4338ca;
            --dark-header: #0f172a; /* Slate 900 */
            --panel-bg: #1e293b; /* Slate 800 */
            --accent: #38bdf8; /* Sky blue */
        }
        
        body, html { height: 100%; margin: 0; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        .editor-row { display: flex; height: calc(100vh - 64px); background: #f8fafc; }
        .editor-canvas { flex-grow: 1; height: 100%; overflow: hidden; position: relative; }
        .panel-right { width: 310px; background: var(--panel-bg); color: #cbd5e1; display: flex; flex-direction: column; z-index: 10; border-left: 1px solid rgba(0,0,0,0.3); }

        #gjs { border: none; }
        .sidebar-section { 
            display: flex; 
            flex-direction: column; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            transition: flex 0.3s ease;
            overflow: hidden;
        }
        .sidebar-section:last-child { border-bottom: none; }
        
        /* Flex distribution when expanded */
        .section-layers { flex: 1; min-height: 40px; }
        .section-blocks { flex: 2; min-height: 40px; }
        .section-props { flex: 2; min-height: 40px; }

        /* Collapsed state */
        .sidebar-section.collapsed { flex: 0 0 40px !important; }
        .sidebar-section.collapsed > div:not(.sidebar-header) { opacity: 0; pointer-events: none; }
        
        .sidebar-header {
            padding: 12px 15px; font-size: 11px; text-transform: uppercase; letter-spacing: 2px;
            font-weight: 800; background: rgba(0,0,0,0.4); color: var(--accent); border-bottom: 1px solid rgba(255,255,255,0.05);
            cursor: pointer; display: flex; justify-content: space-between; align-items: center;
            user-select: none;
        }
        .sidebar-header::after {
            content: '\f107'; font-family: 'FontAwesome'; font-size: 14px; transition: transform 0.3s;
        }
        .collapsed .sidebar-header::after { transform: rotate(-90deg); }
        
        /* Block Styling */
        .gjs-block {
            width: calc(50% - 16px) !important;
            min-height: 90px !important;
            margin: 8px !important;
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
            border-radius: 6px !important;
            transition: all 0.2s !important;
        }
        .gjs-block:hover { background: rgba(255,255,255,0.08) !important; border-color: var(--accent) !important; }
        .gjs-block-svg { width: 30px !important; height: 30px !important; margin-bottom: 10px !important; fill: var(--accent) !important; }
        .gjs-block-label { font-size: 9px !important; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }

        /* GrapesJS UI Overrides */
        .gjs-one-bg { background-color: var(--panel-bg); }
        .gjs-two-bg { background-color: rgba(0,0,0,0.2); }
        .gjs-pn-btn.gjs-pn-active { color: white; background: var(--primary); }

        .page-selector { background: #334155; color: white; border: 1px solid #475569; padding: 5px 12px; border-radius: 6px; font-size: 12px; outline: none; }
        .lang-toggle { font-size: 10px; font-weight: bold; padding: 5px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); }
        .lang-toggle.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* Fix for panel-actions overlap */
        #panel-actions {
            position: relative !important;
            display: flex !important;
            align-items: center;
            flex-shrink: 0;
            width: auto !important;
            height: auto !important;
            background: rgba(30, 41, 59, 0.8) !important; /* slate-800/80 */
        }
        .gjs-pn-buttons {
            display: flex !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .gjs-pn-btn {
            position: relative !important;
            margin: 0 2px !important;
        }
    </style>
</head>
<body class="flex flex-col h-screen">
    
    <header class="h-[64px] bg-[var(--dark-header)] text-white px-8 flex justify-between items-center shadow-lg z-50">
        <div class="flex items-center gap-10">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-[var(--primary)] rounded-lg shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <h1 class="font-black text-lg tracking-tighter text-white uppercase">Web editor</h1>
            </div>

            <div class="flex items-center gap-4 border-l border-white/10 pl-10">
                <div class="flex bg-slate-800 p-1 rounded-lg gap-1">
                    <a href="index.php?lang=cs&page=<?= $currentPage ?>" class="lang-toggle <?= $uiLang === 'cs' ? 'active' : 'opacity-40' ?>">CZ</a>
                    <a href="index.php?lang=en&page=<?= $currentPage ?>" class="lang-toggle <?= $uiLang === 'en' ? 'active' : 'opacity-40' ?>">EN</a>
                </div>
                <select class="page-selector" onchange="window.location.href='index.php?lang=<?= $uiLang ?>&page=' + this.value">
                    <?php foreach ($editableFiles as $file): ?>
                        <option value="<?= $file ?>" <?= $file === $currentPage ? 'selected' : '' ?>>
                            <?= $file ?> | <?= htmlspecialchars($pageTitles[$file]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <!-- Header Icons -->
            <div id="panel-actions" class="flex items-center gap-3 bg-slate-800/80 rounded-lg p-1.5 px-4"></div>
            
            <div class="flex items-center gap-4 border-l border-white/10 pl-8">
                <div id="status-msg" class="text-xs text-sky-400 font-bold opacity-0 transition-opacity">Saved!</div>
                <button id="save-btn" class="bg-[var(--primary)] hover:bg-[var(--primary-dark)] text-white px-8 py-2.5 rounded-lg font-bold text-xs transition-all shadow-lg active:transform active:scale-95 whitespace-nowrap">
                    <?= $uiLang === 'cs' ? 'ULOŽIT ZMĚNY' : 'SAVE CHANGES' ?>
                </button>
                <a href="login.php?logout=1" class="text-xs text-slate-400 hover:text-white transition-colors">Logout</a>
            </div>
        </div>
    </header>

    <div class="editor-row">
        <div class="editor-canvas">
            <div id="gjs"></div>
        </div>

        <div class="panel-right">
            <div class="sidebar-section section-layers">
                <div class="sidebar-header" onclick="this.parentElement.classList.toggle('collapsed')"><?= $uiLang === 'cs' ? 'VRSTVY' : 'LAYERS' ?></div>
                <div id="layers-container" class="flex-1 overflow-y-auto overflow-x-hidden"></div>
            </div>
            
            <div class="sidebar-section section-blocks">
                <div class="sidebar-header border-t border-black/20" onclick="this.parentElement.classList.toggle('collapsed')"><?= $uiLang === 'cs' ? 'KNIHOVNA BLOKŮ' : 'BLOCKS' ?></div>
                <div id="blocks-container" class="flex-1 overflow-y-auto overflow-x-hidden"></div>
            </div>

            <div class="sidebar-section section-props">
                <div class="sidebar-header border-t border-black/20" onclick="this.parentElement.classList.toggle('collapsed')"><?= $uiLang === 'cs' ? 'NASTAVENÍ PRVKU' : 'PROPERTIES' ?></div>
                <div id="styles-container" class="overflow-y-auto overflow-x-hidden"></div>
                <div id="traits-container" class="overflow-y-auto overflow-x-hidden"></div>
            </div>
        </div>
    </div>

    <script>
        window.INITIAL_CONTENT = <?php echo json_encode($content); ?>;
        window.UI_LANG = <?php echo json_encode($uiLang); ?>;
    </script>
    <script src="editor.js"></script>
</body>
</html>
