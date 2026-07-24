<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/AuthManager.php';

if (is_dir($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], -1) !== '/') {
    header('Location: ' . $_SERVER['REQUEST_URI'] . '/');
    exit;
}

if (!AuthManager::isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$uiLang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'cs';

$filesPhp = glob(ROOT_DIR . "*.php") ?: [];
$filesHtml = glob(ROOT_DIR . "*.html") ?: [];
$files = array_merge($filesPhp, $filesHtml);
$editableFiles = [];
$pageTitles = [];

require_once __DIR__ . '/includes/CMS.php';
$pagesConfig = CMS::getPagesConfig();

foreach ($files as $file) {
    $basename = basename($file);
    if (!in_array($basename, ['login.php', 'save.php', 'config.php', 'sw.js', 'router.php', 'version.php'])) {
        $editableFiles[] = $basename;
        
        $title = $pagesConfig[$basename]['title'] ?? '';
        if (empty($title)) {
            $fileContent = @file_get_contents($file);
            if ($fileContent && preg_match('/<title>(.*?)<\/title>/is', $fileContent, $matches)) {
                $rawTitle = trim($matches[1]);
                if (strpos($rawTitle, '<?') === false) {
                    $title = $rawTitle;
                }
            }
        }
        $pageTitles[$basename] = !empty($title) ? $title : $basename;
    }
}

$currentPage = isset($_GET['page']) && in_array($_GET['page'], $editableFiles) ? $_GET['page'] : 'index.php';
if (!in_array($currentPage, $editableFiles) && !empty($editableFiles)) {
    $currentPage = $editableFiles[0];
}

$targetPath = ROOT_DIR . $currentPage;
$rawContent = file_exists($targetPath) ? (file_get_contents($targetPath) ?: '') : '';

$originalTopPhp = '';
if (!empty($rawContent) && preg_match('/^(<\?php[\s\S]*?\?>)/i', trim($rawContent), $matches)) {
    $firstBlock = $matches[1];
    if (strpos($firstBlock, 'CMS::getHeader') === false) {
        $originalTopPhp = $firstBlock;
    }
}

$content = preg_replace('/^<\?php[\s\S]*?\?>/i', '', $rawContent);
$content = preg_replace('/<\?php\s+include\s+[\'"]partials\/footer\.php[\'"];\s*\?>$/i', '', $content);

require_once __DIR__ . '/includes/ThemeManager.php';
$themeManager = new ThemeManager();
$activeThemeBodyClass = $themeManager->getActiveThemeBodyClass();

$initialBodyClass = '';
if (preg_match('/<body[^>]*class=["\']([^"\']*)["\']/i', $content, $bodyClassMatches)) {
    $initialBodyClass = $bodyClassMatches[1];
}
if (empty($initialBodyClass) || $initialBodyClass === 'bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-6') {
    $initialBodyClass = $activeThemeBodyClass;
}

if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $bodyMatches)) {
    $content = $bodyMatches[1];
} else {
    $content = preg_replace('/^[\s\S]*?<head>[\s\S]*?<\/head>/i', '', $content);
    $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
    $content = preg_replace('/<\/?(html|head|body)[^>]*>/i', '', $content);
}

$content = trim($content);
$_SESSION['current_page'] = $currentPage;

?>
<!DOCTYPE html>
<html lang="<?= $uiLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fida CMS | <?= $currentPage ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22%234f46e5%22/><path d=%22M55 35a2.121 2.121 0 0 1 3 3L37 62l-7 2 2-7 21-22z%22 fill=%22none%22 stroke=%22white%22 stroke-width=%225%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22/></svg>">
    
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script src="https://unpkg.com/grapesjs"></script>
    <script>(function(){var w=console.warn;console.warn=function(){if(arguments[0]&&typeof arguments[0]==='string'&&arguments[0].indexOf('cdn.tailwindcss.com')!==-1)return;w.apply(console,arguments);};})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --dark-header: #0f172a;
            --panel-bg: #1e293b;
            --accent: #38bdf8;
        }
        
        body, html { height: 100%; margin: 0; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        .editor-row { display: flex; height: calc(100vh - 64px); background: #f8fafc; }
        .editor-canvas { flex-grow: 1; height: 100%; overflow: hidden; position: relative; }
        .panel-right { width: 310px; background: var(--panel-bg); color: #cbd5e1; display: flex; flex-direction: column; z-index: 10; border-left: 1px solid rgba(0,0,0,0.3); }

        #gjs { border: none; height: 100% !important; width: 100% !important; }
        .gjs-cv-canvas { box-sizing: border-box !important; width: calc(100% - 5px) !important; height: 100% !important; top: 0 !important; left: 0 !important; }

        .right-panel-tabs {
            display: flex;
            background: rgba(0,0,0,0.5);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .right-panel-tab {
            flex: 1;
            padding: 12px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 2px solid transparent;
            background: transparent;
            border-top: none; border-left: none; border-right: none;
        }
        .right-panel-tab:hover {
            color: #94a3b8;
            background: rgba(255,255,255,0.02);
        }
        .right-panel-tab.active {
            color: var(--accent, #6366f1);
            border-bottom-color: var(--accent, #6366f1);
            background: rgba(99, 102, 241, 0.1);
        }

        .tab-content {
            display: none;
            flex: 1;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            height: calc(100% - 45px);
        }
        .tab-content.active {
            display: flex;
        }
        
        .gjs-blocks-c {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
            padding: 4px !important;
        }
        .gjs-block-category {
            margin-bottom: 8px !important;
            width: 100% !important;
        }
        .gjs-block-category .gjs-title, .gjs-category-title, .gjs-title {
            font-size: 10px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            color: var(--accent) !important;
            padding: 6px 8px !important;
            background: rgba(0,0,0,0.3) !important;
            border-radius: 4px !important;
            margin-bottom: 6px !important;
            display: block !important;
            cursor: pointer !important;
        }
        .gjs-block-category .gjs-blocks-c {
            display: flex !important;
            flex-wrap: wrap !important;
        }

        .gjs-block {
            width: calc(50% - 8px) !important; min-height: 65px !important; margin: 4px !important;
            background: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.05) !important;
            border-radius: 6px !important; transition: all 0.2s !important; display: flex !important;
            flex-direction: column !important; align-items: center !important; justify-content: center !important;
            padding: 6px !important; text-align: center !important;
        }
        .gjs-block:hover { background: rgba(255,255,255,0.08) !important; border-color: var(--accent) !important; }
        .gjs-block-svg { width: 20px !important; height: 20px !important; margin: 0 auto 4px auto !important; fill: var(--accent) !important; display: block !important; }
        .gjs-block-label { font-size: 8.5px !important; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; line-height: 1.1 !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; text-align: center !important; width: 100% !important; }

        .gjs-one-bg { background-color: var(--panel-bg); }
        .gjs-two-bg { background-color: rgba(0,0,0,0.2); }
        .gjs-pn-btn.gjs-pn-active { color: white; background: var(--primary); }

        .page-selector { background: #334155; color: white; border: 1px solid #475569; padding: 5px 12px; border-radius: 6px; font-size: 12px; outline: none; }

        .gjs-sm-label, .gjs-sm-field, .gjs-sm-property, .gjs-clm-label, .gjs-field, .gjs-clm-field, .gjs-layer-name, .gjs-sm-title { font-size: 10px !important; }
        .gjs-clm-tags-label { font-size: 9px !important; }
        .gjs-sm-sector-title { font-size: 10px !important; font-weight: 700 !important; }
        .gjs-sm-composite { padding: 4px !important; }
        .gjs-sm-stack { margin: 4px 0 !important; }
        .gjs-sm-property { margin-bottom: 4px !important; }
        .gjs-clm-tag { font-size: 9px !important; padding: 2px 5px !important; }
        .gjs-pn-views-container, .gjs-pn-views { display: none !important; }

        .gjs-pn-commands, .gjs-pn-panel {
            width: 100% !important;
            box-sizing: border-box !important;
        }

        #panel-actions {
            position: relative !important; display: flex !important; align-items: center; flex-shrink: 0;
            width: auto !important; height: auto !important; background: rgba(30, 41, 59, 0.8) !important;
        }
        .gjs-pn-buttons { display: flex !important; position: relative !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .gjs-pn-btn { position: relative !important; margin: 0 2px !important; }

        #sidebar-left { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        #sidebar-left.collapsed { width: 70px !important; }
        #sidebar-left.collapsed .sidebar-text, #sidebar-left.collapsed .sidebar-label { display: none !important; }
        #sidebar-left.collapsed .sidebar-page-btns { flex-direction: column; }
        #sidebar-left.collapsed .sidebar-page-btns button span { display: none !important; }
        #sidebar-left.collapsed select { padding: 8px 4px !important; font-size: 10px !important; text-align: center; }
        #sidebar-left.collapsed .sidebar-bottom-row { flex-direction: column; }

        /* Fast Custom Tooltips */
        [data-tooltip] { position: relative; }
        [data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: -36px;
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            background: #0f172a;
            color: #f8fafc;
            border: 1px solid #334155;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.08s ease, transform 0.08s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        [data-tooltip]:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* Expanded & Resizable GrapesJS Code Modal (viewCode) */
        .gjs-mdl-dialog {
            width: 90vw !important;
            max-width: 1350px !important;
            max-height: 92vh !important;
            border-radius: 16px !important;
            background: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            transition: all 0.2s ease !important;
        }
        .gjs-mdl-dialog.gjs-mdl-fullscreen {
            width: 98vw !important;
            height: 96vh !important;
            max-width: 98vw !important;
            max-height: 96vh !important;
        }
        .gjs-mdl-header {
            background: #020617 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding: 14px 20px !important;
            color: #f8fafc !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .gjs-mdl-title {
            color: #f8fafc !important;
            font-weight: 800 !important;
        }
        .gjs-mdl-btn-close {
            color: #94a3b8 !important;
            font-size: 18px !important;
            padding: 4px 10px !important;
            border-radius: 8px !important;
            transition: all 0.15s !important;
            cursor: pointer !important;
        }
        .gjs-mdl-btn-close:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1) !important;
        }
        .gjs-mdl-content {
            padding: 16px !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            background: #090d16 !important;
        }
        .gjs-export-dl, .gjs-cm-editor-c, .gjs-mdl-content textarea, .gjs-mdl-content .CodeMirror {
            flex: 1 !important;
            height: 70vh !important;
            min-height: 500px !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
            background: #020617 !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 14px !important;
            outline: none !important;
            resize: vertical !important;
        }
        .gjs-cm-editor-c .CodeMirror {
            font-size: 14px !important;
            line-height: 1.6 !important;
        }

        /* GrapesJS Asset Manager UI */
        .gjs-am-assets-header { background: #020617 !important; border-bottom: 1px solid rgba(255,255,255,0.1) !important; padding: 12px !important; }
        .gjs-am-assets { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important; gap: 12px !important; padding: 12px !important; }
        .gjs-am-asset { width: 100% !important; height: 125px !important; border-radius: 12px !important; background: #020617 !important; border: 1px solid rgba(255,255,255,0.1) !important; overflow: hidden !important; transition: all 0.2s !important; margin: 0 !important; }
        .gjs-am-asset:hover { border-color: var(--accent) !important; transform: translateY(-2px); }
        .gjs-am-asset-image { height: 95px !important; background-size: cover !important; background-position: center !important; }
        .gjs-am-meta { padding: 4px 6px !important; font-size: 10px !important; color: #cbd5e1 !important; text-align: center !important; text-overflow: ellipsis !important; white-space: nowrap !important; overflow: hidden !important; }
    </style>
</head>
<body class="flex h-screen w-screen overflow-hidden bg-slate-950 text-slate-100">
    
    <!-- Page Settings Modal -->
    <div id="settings-modal" class="hidden fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4">
        <div class="bg-slate-800 w-full max-w-lg rounded-xl shadow-2xl border border-white/10 overflow-hidden">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h2 class="text-white font-bold uppercase tracking-wider"><?= $uiLang === 'cs' ? 'Nastavení stránky' : 'Page Settings' ?></h2>
                <button onclick="closePageSettings()" class="text-slate-400 hover:text-white"><i class="fa fa-times"></i></button>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">URL Slug (např. o-nas)</label>
                    <input type="text" id="meta-slug" class="w-full bg-slate-900 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-[var(--primary)]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">SEO Titulek</label>
                    <input type="text" id="meta-title" class="w-full bg-slate-900 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-[var(--primary)]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">SEO Popis (Description)</label>
                    <textarea id="meta-description" rows="3" class="w-full bg-slate-900 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-[var(--primary)]"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Klíčová slova (Keywords)</label>
                    <input type="text" id="meta-keywords" class="w-full bg-slate-900 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-[var(--primary)]">
                </div>
            </div>
            <div class="p-6 bg-slate-900/50 flex justify-end gap-4">
                <button onclick="closePageSettings()" class="px-6 py-2 text-slate-400 hover:text-white font-bold text-xs uppercase"><?= $uiLang === 'cs' ? 'Zrušit' : 'Cancel' ?></button>
                <button onclick="savePageSettings()" class="bg-[var(--primary)] hover:bg-[var(--primary-dark)] text-white px-8 py-2 rounded-lg font-bold text-xs uppercase shadow-lg transition-all"><?= $uiLang === 'cs' ? 'OK' : 'OK' ?></button>
            </div>
        </div>
    </div>

    <!-- SMTP Settings Modal -->
    <div id="smtp-modal" class="hidden fixed inset-0 bg-black/75 z-[110] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-white/10 overflow-hidden text-slate-200">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-slate-950">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-extrabold text-base tracking-tight">Nastavení SMTP Maileru</h2>
                        <p class="text-xs text-slate-400">Přesměrování odcházejících e-mailů přes vlastní SMTP server</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="openPluginHelpModal('smtp-mailer/smtp-mailer.php')" class="bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white px-2.5 py-1 rounded-lg border border-indigo-500/30 text-xs font-bold transition-all flex items-center gap-1.5" title="Nápověda k použití pluginu">
                        <i class="fa fa-question-circle"></i> Nápověda
                    </button>
                    <button onclick="closeSMTPModal()" class="text-slate-400 hover:text-white"><i class="fa fa-times text-lg"></i></button>
                </div>
            </div>
            
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">SMTP Host / Server *</label>
                        <input type="text" id="smtp-host" placeholder="např. smtp.seznam.cz" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">Port *</label>
                        <input type="number" id="smtp-port" placeholder="587 / 465" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">Šifrování</label>
                        <select id="smtp-encryption" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500">
                            <option value="tls">TLS (port 587)</option>
                            <option value="ssl">SSL (port 465)</option>
                            <option value="none">Žádné (port 25)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">E-mail odesílatele</label>
                        <input type="email" id="smtp-from-email" placeholder="info@elektroservisplzen.cz" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">SMTP Uživatel (Přihlášení)</label>
                        <input type="text" id="smtp-username" placeholder="vaše jméno nebo e-mail" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1.5">SMTP Heslo</label>
                        <input type="password" id="smtp-password" placeholder="••••••••••••" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-300 uppercase mb-1.5">Jméno Odesílatele</label>
                    <input type="text" id="smtp-from-name" placeholder="Elektroservis Plzeň" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white outline-none focus:border-indigo-500">
                </div>

                <div class="pt-2 border-t border-white/5 flex items-center justify-between gap-3">
                    <span class="text-[11px] text-slate-400">Před uložením můžete spojení otestovat:</span>
                    <button type="button" onclick="testSMTPConnection()" id="btn-test-smtp" class="bg-slate-800 hover:bg-slate-700 text-indigo-300 font-bold px-4 py-2.5 rounded-xl border border-indigo-500/30 transition-all text-xs flex items-center gap-1.5">
                        <i class="fa fa-paper-plane"></i> Otestovat spojení
                    </button>
                </div>
            </div>

            <div class="p-5 bg-slate-950 border-t border-white/10 flex justify-between items-center">
                <button onclick="closeSMTPModal()" class="px-5 py-2.5 text-slate-400 hover:text-white font-bold text-xs uppercase">Zrušit</button>
                <button onclick="saveSMTPConfig()" id="btn-save-smtp" class="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold px-6 py-2.5 rounded-xl shadow-lg transition-all text-xs uppercase">Uložit Nastavení SMTP</button>
            </div>
        </div>
    </div>

    <!-- Plugin Help Modal -->
    <div id="plugin-help-modal" class="hidden fixed inset-0 bg-black/75 z-[120] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-slate-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-white/10 overflow-hidden text-slate-200">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-slate-950">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-lg">
                        <i class="fa fa-question-circle"></i>
                    </div>
                    <div>
                        <h2 id="plugin-help-title" class="text-white font-extrabold text-base tracking-tight">Nápověda k pluginu</h2>
                        <p id="plugin-help-subtitle" class="text-xs text-slate-400">Návod k použití a integraci pluginu v projektu</p>
                    </div>
                </div>
                <button onclick="closePluginHelpModal()" class="text-slate-400 hover:text-white transition-colors"><i class="fa fa-times text-lg"></i></button>
            </div>
            
            <div id="plugin-help-content" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs leading-relaxed">
                <!-- Dynamicky plněný obsah -->
            </div>

            <div class="p-4 bg-slate-950 border-t border-white/10 flex justify-end">
                <button onclick="closePluginHelpModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold px-6 py-2 rounded-xl shadow-lg transition-all text-xs uppercase">Rozumím</button>
            </div>
        </div>
    </div>

    <!-- Generic Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black/75 z-[200] flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-200">
        <div class="bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-white/10 overflow-hidden text-slate-200 transform transition-all scale-100">
            <div class="p-6 text-center space-y-4">
                <div id="confirm-icon-box" class="w-14 h-14 rounded-2xl bg-red-500/20 text-red-400 border border-red-500/30 flex items-center justify-center mx-auto text-2xl shadow-lg">
                    <i id="confirm-icon" class="fa fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 id="confirm-title" class="text-lg font-black text-white uppercase tracking-tight">Potvrdit akci</h3>
                    <p id="confirm-message" class="text-xs text-slate-400 mt-2 leading-relaxed">Opravdu chcete provést tuto akci?</p>
                </div>
            </div>
            <div class="p-4 bg-slate-950/80 border-t border-white/5 flex items-center justify-end gap-3">
                <button id="confirm-cancel-btn" onclick="closeConfirmModal()" class="px-5 py-2.5 text-slate-400 hover:text-white font-bold text-xs uppercase transition-all">Zrušit</button>
                <button id="confirm-action-btn" class="bg-red-600 hover:bg-red-500 text-white font-black px-6 py-2.5 rounded-xl shadow-lg shadow-red-600/20 active:transform active:scale-[0.98] transition-all text-xs uppercase">Potvrdit</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[300] flex flex-col gap-3 pointer-events-none"></div>

    <!-- Collapsible Left Sidebar -->
    <aside id="sidebar-left" class="w-[260px] h-screen bg-slate-900 border-r border-white/5 flex flex-col justify-between transition-all duration-300 shrink-0 z-30">
        <div class="p-5 border-b border-white/5 flex items-center gap-3 sidebar-logo-container">
            <div class="p-2 bg-[var(--primary)] rounded-lg shadow-lg shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="sidebar-text flex flex-col gap-0.5 overflow-hidden">
                <h1 class="group font-black text-sm tracking-tight text-white uppercase flex items-center gap-1.5 cursor-default select-none whitespace-nowrap">
                    Fida CMS
                    <span class="opacity-100 text-[9px] bg-indigo-500/30 text-indigo-300 border border-indigo-500/20 px-1.5 py-0.5 rounded font-semibold">
                        v<?= CMS_VERSION ?>
                    </span>
                </h1>
            </div>
        </div>

        <div class="flex flex-col gap-1 p-4 overflow-y-auto flex-1">
            <div class="flex flex-col gap-1">
                <button id="menu-btn-editor" onclick="switchView('editor')" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10">
                    <i class="fa fa-files-o w-4 text-center"></i>
                    <span class="sidebar-text">Stránky</span>
                </button>

                <button id="menu-btn-files" onclick="switchView('files')" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors text-left">
                    <i class="fa fa-folder-open w-4 text-center text-indigo-400"></i>
                    <span class="sidebar-text">Soubory</span>
                </button>

                <button id="menu-btn-plugins" onclick="switchView('plugins')" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors text-left">
                    <i class="fa fa-plug w-4 text-center text-indigo-400"></i>
                    <span class="sidebar-text">Pluginy</span>
                </button>

                <button id="menu-btn-themes" onclick="switchView('themes')" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors text-left">
                    <i class="fa fa-paint-brush w-4 text-center text-indigo-400"></i>
                    <span class="sidebar-text">Vzhledy</span>
                </button>

                <button id="menu-btn-settings" onclick="switchView('settings')" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors text-left">
                    <i class="fa fa-globe w-4 text-center text-indigo-400"></i>
                    <span class="sidebar-text">Nastavení</span>
                </button>
            </div>

            <div id="update-banner" class="hidden px-2 mt-4">
                <button onclick="switchView('settings'); switchSettingsTab('updates');" class="w-full text-[10px] bg-amber-500 hover:bg-amber-600 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm flex items-center justify-center gap-2 transition-all">
                    <i class="fa fa-refresh"></i> UPDATE AVAILABLE
                </button>
            </div>
        </div>

        <div class="p-3 border-t border-white/5 flex flex-col gap-3">
            <div>
                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 sidebar-label text-center">Jazyk CMS</div>
                <div class="flex gap-1.5">
                    <a href="index.php?lang=cs&page=<?= $currentPage ?>" class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-xs font-bold transition-all border <?= $uiLang === 'cs' ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-800 border-white/5 text-slate-400 hover:text-white' ?>">
                        <span>🇨🇿</span> <span class="sidebar-text">CZ</span>
                    </a>
                    <a href="index.php?lang=en&page=<?= $currentPage ?>" class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-xs font-bold transition-all border <?= $uiLang === 'en' ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-800 border-white/5 text-slate-400 hover:text-white' ?>">
                        <span>🇬🇧</span> <span class="sidebar-text">EN</span>
                    </a>
                </div>
            </div>

            <div class="flex gap-2 sidebar-bottom-row">
                <button id="sidebar-toggle-btn" onclick="toggleSidebar()" class="flex-1 flex items-center justify-center gap-2 py-2 text-xs text-slate-400 hover:text-white bg-slate-800/50 hover:bg-slate-800 rounded-lg transition-all" title="Zmenšit panel">
                    <i id="sidebar-toggle-icon" class="fa fa-angle-double-left"></i>
                    <span class="sidebar-text">Zmenšit panel</span>
                </button>
                <a href="login.php?logout=1" class="bg-red-950/40 hover:bg-red-600 text-red-200 hover:text-white py-2 px-3.5 rounded-lg flex items-center justify-center transition-all shadow-md active:transform active:scale-95" title="Odhlásit se">
                    <i class="fa fa-sign-out"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Right Workspace Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="h-[64px] bg-[var(--dark-header)] text-white px-8 flex justify-between items-center shadow-lg z-40 shrink-0">
            <div class="flex items-center gap-3">
                <select class="page-selector" title="<?= $uiLang === 'cs' ? 'Vybrat stránku ke úpravě' : 'Select page to edit' ?>" onchange="window.location.href='index.php?lang=<?= $uiLang ?>&page=' + this.value">
                    <?php foreach ($editableFiles as $file): ?>
                        <option value="<?= $file ?>" <?= $file === $currentPage ? 'selected' : '' ?>>
                            <?= $file ?> | <?= htmlspecialchars($pageTitles[$file]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button onclick="createNewPage()" class="bg-slate-800 hover:bg-slate-700 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-lg active:transform active:scale-95" title="<?= $uiLang === 'cs' ? 'Vytvořit novou stránku' : 'Create new page' ?>">
                    <i class="fa fa-plus"></i>
                </button>
                <button onclick="openPageSettings()" class="bg-slate-800 hover:bg-slate-700 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-lg active:transform active:scale-95" title="<?= $uiLang === 'cs' ? 'Nastavení stránky' : 'Page settings' ?>">
                    <i class="fa fa-cog text-indigo-400"></i>
                </button>
                <a href="../<?= urlencode($currentPage) ?>" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-lg active:transform active:scale-95" title="<?= $uiLang === 'cs' ? 'Náhled na web (otevře v novém okně)' : 'Preview website (opens in new window)' ?>">
                    <i class="fa fa-external-link text-emerald-400"></i>
                </a>
                <?php if ($currentPage !== 'index.php'): ?>
                <button onclick="deleteCurrentPage()" class="bg-red-900/40 hover:bg-red-600 text-red-200 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-lg active:transform active:scale-95" title="<?= $uiLang === 'cs' ? 'Smazat tuto stránku' : 'Delete this page' ?>">
                    <i class="fa fa-trash"></i>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-8">
                <div id="panel-actions" class="flex items-center gap-3 bg-slate-800/80 rounded-lg p-1.5 px-4"></div>
                
                <div class="flex items-center gap-4 border-l border-white/10 pl-8">
                    <div id="status-msg" class="text-xs text-sky-400 font-bold opacity-0 transition-opacity">Saved!</div>
                    <button id="save-btn" class="bg-[var(--primary)] hover:bg-[var(--primary-dark)] text-white px-8 py-2.5 rounded-lg font-bold text-xs transition-all shadow-lg active:transform active:scale-95 whitespace-nowrap">
                        <?= $uiLang === 'cs' ? 'ULOŽIT ZMĚNY' : 'SAVE CHANGES' ?>
                    </button>
                </div>
            </div>
        </header>

        <div class="editor-row flex flex-1 h-[calc(100vh-64px)] overflow-hidden relative">
            <div class="editor-canvas">
                <div id="gjs"></div>
            </div>

            <!-- Settings Page -->
            <div id="settings-page-wrapper" class="hidden flex-1 h-full bg-slate-950 overflow-y-auto p-12 text-slate-200">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-black text-white uppercase tracking-tight">Globální nastavení webu</h1>
                        <p class="text-slate-400 text-sm mt-1">Upravte kontaktní údaje, měřicí kódy a chybové stránky pro celý web.</p>
                    </div>

                    <div class="flex gap-2 border-b border-white/5 mb-8 pb-px">
                        <button onclick="switchSettingsTab('general')" id="btn-tab-general" class="settings-tab-btn px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 border-indigo-500 text-white transition-all">Obecné</button>
                        <button onclick="switchSettingsTab('contacts')" id="btn-tab-contacts" class="settings-tab-btn px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-white transition-all">Kontakty</button>
                        <button onclick="switchSettingsTab('addresses')" id="btn-tab-addresses" class="settings-tab-btn px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-white transition-all">Adresy</button>
                        <button onclick="switchSettingsTab('cache')" id="btn-tab-cache" class="settings-tab-btn px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-white transition-all">Cache & Rychlost</button>
                        <button onclick="switchSettingsTab('updates')" id="btn-tab-updates" class="settings-tab-btn px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-white transition-all">Aktualizace</button>
                    </div>

                    <div class="space-y-8">
                        <div id="content-tab-general" class="space-y-6">
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
                                    <i class="fa fa-info-circle text-indigo-400"></i> Obecné informace
                                </h2>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Název webu</label>
                                    <input type="text" id="site-name" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Favicon (Ikona webu)</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg bg-slate-950 border border-white/10 flex items-center justify-center p-1.5 overflow-hidden shrink-0">
                                            <img id="site-favicon-preview" src="" alt="Favicon" class="max-w-full max-h-full object-contain" onerror="this.style.display='none'">
                                        </div>
                                        <input type="text" id="site-favicon" oninput="document.getElementById('site-favicon-preview').src = this.value; document.getElementById('site-favicon-preview').style.display='block';" class="flex-1 bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all placeholder-slate-700" placeholder="assets/favicon.png nebo URL ikony">
                                        <label class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-3 rounded-lg cursor-pointer transition-all flex items-center gap-2 shrink-0">
                                            <i class="fa fa-upload"></i> Nahrát Ikonu
                                            <input type="file" id="favicon-file-input" accept="image/*,.ico" class="hidden" onchange="uploadFaviconFile(this)">
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Google Analytics ID</label>
                                    <input type="text" id="site-ga-id" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all placeholder-slate-700" placeholder="G-XXXXXXXXXX">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">404 Chybová stránka</label>
                                    <select id="site-404" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                        <option value="">-- Výchozí --</option>
                                        <?php foreach ($editableFiles as $file): ?>
                                            <option value="<?= $file ?>"><?= $file ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="content-tab-contacts" class="hidden space-y-6">
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
                                    <i class="fa fa-phone text-indigo-400"></i> Kontaktní údaje
                                </h2>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Dispečink nonstop (telefon)</label>
                                    <input type="text" id="site-phone-nonstop" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pevná linka / Kancelář</label>
                                    <input type="text" id="site-phone-landline" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Kontaktní E-mail</label>
                                    <input type="email" id="site-email" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Příjemce zpráv z formulářů</label>
                                    <input type="email" id="site-contact-form-recipient" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm transition-all">
                                </div>
                            </div>
                        </div>

                        <div id="content-tab-addresses" class="hidden space-y-6">
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
                                    <i class="fa fa-map-marker text-indigo-400"></i> Adresy a provozovny
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Sídlo firmy (adresa, lze HTML)</label>
                                        <textarea id="site-address-headquarters" rows="5" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm font-mono transition-all"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Výjezdové stanoviště (adresa, lze HTML)</label>
                                        <textarea id="site-address-dispatch" rows="5" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm font-mono transition-all"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="content-tab-cache" class="hidden space-y-6">
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
                                    <i class="fa fa-flash text-indigo-400"></i> Statické HTML Cacheování
                                </h2>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-white">Zapnout statické cacheování</h3>
                                        <p class="text-xs text-slate-400 mt-1">
                                            Vytvoří předgenerované HTML soubory pro extrémně rychlé načítání stránek.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="checkbox" id="site-enable-cache" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-950 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white"></div>
                                    </label>
                                </div>
                                
                                <div class="pt-6 border-t border-white/5 flex flex-col gap-4">
                                    <h3 class="text-sm font-semibold text-white">Ruční přegenerování cache</h3>
                                    <div>
                                        <button onclick="rebuildCacheManual()" id="rebuild-cache-btn" class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-5 py-2.5 rounded-lg border border-white/5 hover:border-white/10 transition-all flex items-center gap-2 shadow-lg">
                                            <i class="fa fa-refresh"></i> Přegenerovat celou cache
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="content-tab-updates" class="hidden space-y-6">
                            <!-- CMS Core Updates & Core Repo Section -->
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
                                    <i class="fa fa-cloud-download text-indigo-400"></i> Aktualizace Jádra Fida CMS
                                </h2>

                                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 bg-slate-950/60 rounded-xl border border-white/5">
                                    <div>
                                        <div class="text-xs text-slate-400 uppercase font-bold tracking-wider">Verze Jádra CMS</div>
                                        <div class="text-2xl font-black text-indigo-400 mt-1">v<?= APP_VERSION ?></div>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        <button onclick="checkUpdatesManual()" id="btn-check-updates" class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-5 py-3 rounded-lg border border-white/5 transition-all flex items-center gap-2">
                                            <i class="fa fa-search"></i> Zkontrolovat nové verze
                                        </button>
                                        <button onclick="runUpdate()" id="btn-run-update" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-6 py-3 rounded-lg shadow-lg transition-all flex items-center gap-2">
                                            <i class="fa fa-refresh"></i> Vynutit aktualizaci jádra z GitHubu
                                        </button>
                                    </div>
                                </div>

                                <div id="update-status-box" class="hidden p-4 rounded-xl text-sm font-medium border"></div>

                                <div class="pt-4 border-t border-white/5 space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">URL Repozitáře Jádra CMS (CMS_REPO_URL)</label>
                                        <input type="text" id="cms-repo-url-input" value="<?= htmlspecialchars(defined('CMS_REPO_URL') ? CMS_REPO_URL : 'https://github.com/milanknez/fida-cms.git') ?>" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm font-mono transition-all">
                                        <p class="text-xs text-slate-500 mt-1.5">Oficiální repozitář se systémovými aktualizacemi Fida CMS.</p>
                                    </div>
                                    <div class="flex justify-end">
                                        <button onclick="saveCmsRepoSettings()" id="btn-save-cms-repo" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-lg font-bold text-xs uppercase shadow-lg shadow-indigo-600/20 transition-all">
                                            Uložit repozitář jádra CMS
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Content Repository Section -->
                            <div class="bg-slate-900 border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                                <div class="flex items-center justify-between border-b border-white/5 pb-3">
                                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fa fa-github text-indigo-400"></i> Repozitář tohoto Projektu (Webu)
                                    </h2>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <span class="text-xs font-bold text-slate-400 uppercase">Git synchronizace projektu</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="enable-project-git-toggle" <?= (!defined('ENABLE_PROJECT_GIT') || ENABLE_PROJECT_GIT) ? 'checked' : '' ?> class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-950 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white"></div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">URL Projektového Repozitáře (PROJECT_REPO_URL)</label>
                                    <input type="text" id="project-repo-url-input" value="<?= htmlspecialchars(defined('PROJECT_REPO_URL') ? PROJECT_REPO_URL : (defined('REPO_URL') ? REPO_URL : '')) ?>" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm font-mono transition-all" placeholder="https://github.com/uzivatel/moje-stranky.git">
                                    <p class="text-xs text-slate-500 mt-1.5">GitHub repozitář tohoto konkrétního webu, do kterého CMS ukládá změny obsahu a souborů (pro lokální projekty lze vypnout přepínačem výše).</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">GitHub Personal Access Token (GITHUB_TOKEN)</label>
                                    <input type="password" id="github-token-input" value="<?= htmlspecialchars(defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '') ?>" class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-white outline-none focus:border-indigo-500 text-sm font-mono transition-all placeholder-slate-700" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx">
                                    <p class="text-xs text-slate-500 mt-1.5">Token s právy k zápisu do projektového repozitáře.</p>
                                </div>

                                <div class="pt-4 border-t border-white/5 flex justify-end">
                                    <button onclick="saveProjectRepoSettings()" id="btn-save-project-repo" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-lg font-bold text-xs uppercase shadow-lg shadow-indigo-600/20 active:transform active:scale-[0.98] transition-all">
                                        Uložit projektový repozitář
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4 pb-12 border-t border-white/5 pt-8">
                        <button onclick="switchView('editor')" class="px-6 py-3 text-slate-400 hover:text-white font-bold text-xs uppercase transition-colors">Zpět do editoru</button>
                        <button onclick="saveGlobalSettings()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-lg font-bold text-xs uppercase shadow-lg shadow-indigo-600/20 active:transform active:scale-[0.98] transition-all">Uložit nastavení</button>
                    </div>
                </div>
            </div>

            <!-- File Manager Page -->
            <div id="files-page-wrapper" class="hidden flex-1 h-full bg-slate-950 overflow-y-auto p-8 lg:p-12 text-slate-200">
                <div class="max-w-7xl mx-auto space-y-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/5 pb-6">
                        <div>
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-3">
                                Správa souborů
                                <span id="files-count-badge" class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-full font-semibold">0 souborů</span>
                            </h1>
                            <p class="text-slate-400 text-sm mt-1">Nahrávejte, prohlížejte a spravujte obrázky a dokumenty pro váš web.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="file" id="fm-file-input" multiple class="hidden" onchange="handleFileUpload(this.files)">
                            <button onclick="document.getElementById('fm-file-input').click()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2.5 transition-all active:scale-95 text-xs uppercase tracking-wider">
                                <i class="fa fa-upload text-sm"></i> Nahrát nové soubory
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar & Filters -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 bg-slate-900 border border-white/5 p-4 rounded-2xl shadow-xl">
                        <!-- Category Filter Tabs -->
                        <div class="flex items-center gap-1 overflow-x-auto pb-2 md:pb-0">
                            <button onclick="setFmFilter('all')" id="fm-tab-all" class="fm-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all bg-indigo-600 text-white shadow">Všechny</button>
                            <button onclick="setFmFilter('image')" id="fm-tab-image" class="fm-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-white/5">Obrázky</button>
                            <button onclick="setFmFilter('document')" id="fm-tab-document" class="fm-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-white/5">Dokumenty</button>
                            <button onclick="setFmFilter('other')" id="fm-tab-other" class="fm-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-white/5">Ostatní</button>
                        </div>

                        <!-- Search & View Mode -->
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1 md:w-64">
                                <i class="fa fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                <input type="text" id="fm-search-input" oninput="renderFileManager()" placeholder="Hledat soubor..." class="w-full bg-slate-950 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-xs text-white outline-none focus:border-indigo-500 transition-all">
                            </div>
                            <div class="flex items-center bg-slate-950 p-1 border border-white/10 rounded-xl">
                                <button onclick="setFmViewMode('grid')" id="fm-view-grid-btn" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white transition-all" title="Mřížka"><i class="fa fa-th-large"></i></button>
                                <button onclick="setFmViewMode('list')" id="fm-view-list-btn" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-white transition-all" title="Seznam"><i class="fa fa-list"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Drop Zone -->
                    <div id="fm-dropzone" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)" class="border-2 border-dashed border-white/10 hover:border-indigo-500/50 bg-slate-900/40 hover:bg-indigo-950/20 rounded-2xl p-8 text-center transition-all cursor-pointer group" onclick="document.getElementById('fm-file-input').click()">
                        <div class="w-12 h-12 bg-indigo-600/10 group-hover:bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
                            <i class="fa fa-cloud-upload text-xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">Přetáhněte sem soubory pro nahrání</h3>
                        <p class="text-xs text-slate-400">Podporuje obrázky (JPG, PNG, WEBP, SVG), PDF, dokumenty a další (max. 50 MB / soubor)</p>
                        <div id="fm-upload-progress" class="hidden mt-4 max-w-md mx-auto">
                            <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                <span id="fm-upload-status-text">Nahrávám soubory...</span>
                                <span id="fm-upload-percent-text">0%</span>
                            </div>
                            <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden border border-white/5">
                                <div id="fm-upload-bar" class="bg-indigo-600 h-full w-0 transition-all duration-200"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Bar -->
                    <div class="flex justify-between items-center text-xs text-slate-400 px-1">
                        <span id="fm-stats-text">Načítám soubory...</span>
                        <button onclick="loadFileManagerFiles()" class="hover:text-indigo-400 transition-colors flex items-center gap-1.5"><i class="fa fa-refresh"></i> Obnovit</button>
                    </div>

                    <!-- Container for Grid or List -->
                    <div id="fm-files-container" class="min-h-[250px]">
                        <!-- Dynamic file cards injected here -->
                    </div>
                </div>
            </div>

            <!-- File Preview Modal -->
            <div id="fm-preview-modal" class="hidden fixed inset-0 bg-black/80 z-[120] flex items-center justify-center p-4 backdrop-blur-sm">
                <div class="bg-slate-900 w-full max-w-3xl rounded-2xl shadow-2xl border border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-4 border-b border-white/10 flex justify-between items-center bg-slate-950/50">
                        <h3 id="fm-preview-title" class="font-bold text-white text-sm truncate pr-4">Náhled souboru</h3>
                        <button onclick="closeFmPreviewModal()" class="text-slate-400 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5 transition-all"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto flex flex-col items-center justify-center bg-slate-950/80">
                        <div id="fm-preview-content" class="w-full flex items-center justify-center">
                            <!-- Image or Icon -->
                        </div>
                    </div>
                    <div class="p-4 bg-slate-900 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div id="fm-preview-meta" class="text-xs text-slate-400 font-mono"></div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button id="fm-preview-copy-btn" onclick="" class="flex-1 sm:flex-none bg-slate-800 hover:bg-slate-700 text-white px-5 py-2 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2">
                                <i class="fa fa-copy"></i> Kopírovat URL
                            </button>
                            <a id="fm-preview-open-link" href="#" target="_blank" class="flex-1 sm:flex-none bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2">
                                <i class="fa fa-external-link"></i> Otevřít v okně
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plugins Manager Page -->
            <div id="plugins-page-wrapper" class="hidden flex-1 h-full bg-slate-950 overflow-y-auto p-8 lg:p-12 text-slate-200">
                <div class="max-w-7xl mx-auto space-y-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/5 pb-6">
                        <div>
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-3">
                                Správa pluginů
                                <span id="plugins-count-badge" class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-full font-semibold">0 aktivních</span>
                            </h1>
                            <p class="text-slate-400 text-sm mt-1">PHP doplňky a rozšíření na míru pro váš web. Nahrajte balíček .zip nebo .php skript.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="file" id="plugin-file-input" accept=".zip,.php" class="hidden" onchange="handlePluginUpload(this.files)">
                            <button onclick="document.getElementById('plugin-file-input').click()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2.5 transition-all active:scale-95 text-xs uppercase tracking-wider">
                                <i class="fa fa-plus-circle text-sm"></i> Nahrát nový plugin (.zip / .php)
                            </button>
                        </div>
                    </div>

                    <!-- Filter & Search Toolbar -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 bg-slate-900 border border-white/5 p-4 rounded-2xl shadow-xl">
                        <div class="flex items-center gap-1 overflow-x-auto">
                            <button onclick="setPluginFilter('all')" id="plugin-tab-all" class="plugin-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all bg-indigo-600 text-white shadow">Všechny</button>
                            <button onclick="setPluginFilter('active')" id="plugin-tab-active" class="plugin-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-white/5">Aktivní</button>
                            <button onclick="setPluginFilter('inactive')" id="plugin-tab-inactive" class="plugin-filter-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white hover:bg-white/5">Neaktivní</button>
                        </div>
                        <div class="relative flex-1 md:w-64">
                            <i class="fa fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                            <input type="text" id="plugin-search-input" oninput="renderPlugins()" placeholder="Hledat plugin..." class="w-full bg-slate-950 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-xs text-white outline-none focus:border-indigo-500 transition-all">
                        </div>
                    </div>

                    <!-- Plugins List Container -->
                    <div id="plugins-container" class="min-h-[250px] space-y-4">
                        <!-- Dynamic Plugin Cards injected here -->
                    </div>
                </div>
            </div>

            <!-- Themes Manager Page -->
            <div id="themes-page-wrapper" class="hidden flex-1 h-full bg-slate-950 overflow-y-auto p-8 lg:p-12 text-slate-200">
                <div class="max-w-7xl mx-auto space-y-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/5 pb-6">
                        <div>
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-3">
                                Správa Vzhledů
                                <span id="active-theme-badge" class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-full font-semibold">Default</span>
                            </h1>
                            <p class="text-slate-400 text-sm mt-1">Správa témat webu, úprava společné hlavičky a patičky pro všechny stránky.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="file" id="theme-file-input" accept=".zip" class="hidden" onchange="handleThemeUpload(this.files)">
                            <button onclick="document.getElementById('theme-file-input').click()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2.5 transition-all active:scale-95 text-xs uppercase tracking-wider">
                                <i class="fa fa-upload text-sm"></i> Nahrát nový vzhled (.zip)
                            </button>
                        </div>
                    </div>

                    <!-- Quick Layout Edit Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 flex flex-col justify-between hover:border-indigo-500/50 transition-all shadow-xl">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-3 bg-indigo-600/20 text-indigo-400 rounded-xl border border-indigo-500/30">
                                        <i class="fa fa-header text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Společná Hlavička (Header)</h3>
                                        <p class="text-xs text-slate-400">Obsahuje navigaci, logo a hlavičkové prvky webu.</p>
                                    </div>
                                </div>
                            </div>
                            <button onclick="editThemeHeader()" class="mt-4 w-full bg-slate-800 hover:bg-indigo-600 text-white font-bold py-2.5 rounded-xl transition-all text-xs flex items-center justify-center gap-2">
                                <i class="fa fa-pencil"></i> Upravit Hlavičku v GrapesJS
                            </button>
                        </div>

                        <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 flex flex-col justify-between hover:border-indigo-500/50 transition-all shadow-xl">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-3 bg-indigo-600/20 text-indigo-400 rounded-xl border border-indigo-500/30">
                                        <i class="fa fa-window-minimize text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Společná Patička (Footer)</h3>
                                        <p class="text-xs text-slate-400">Obsahuje patku, copyright, kontakty a patičkové skripty.</p>
                                    </div>
                                </div>
                            </div>
                            <button onclick="editThemeFooter()" class="mt-4 w-full bg-slate-800 hover:bg-indigo-600 text-white font-bold py-2.5 rounded-xl transition-all text-xs flex items-center justify-center gap-2">
                                <i class="fa fa-pencil"></i> Upravit Patičku v GrapesJS
                            </button>
                        </div>
                    </div>

                    <!-- Themes Gallery -->
                    <div>
                        <h2 class="text-xl font-bold text-white mb-4">Dostupné Vzhledy (Témata)</h2>
                        <div id="themes-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Dynamic theme cards injected here -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-right">
                <div class="right-panel-tabs">
                    <button class="right-panel-tab active" onclick="switchRightTab('blocks')" id="tab-btn-blocks" data-tooltip="<?= $uiLang === 'cs' ? 'Knihovna Bloků' : 'Blocks' ?>">
                        <i class="fa fa-th-large text-sm"></i>
                    </button>
                    <button class="right-panel-tab" onclick="switchRightTab('styles')" id="tab-btn-styles" data-tooltip="<?= $uiLang === 'cs' ? 'Styly a Vzhled' : 'Styles' ?>">
                        <i class="fa fa-paint-brush text-sm"></i>
                    </button>
                    <button class="right-panel-tab" onclick="switchRightTab('traits')" id="tab-btn-traits" data-tooltip="<?= $uiLang === 'cs' ? 'Nastavení Prvku' : 'Settings' ?>">
                        <i class="fa fa-cog text-sm"></i>
                    </button>
                    <button class="right-panel-tab" onclick="switchRightTab('layers')" id="tab-btn-layers" data-tooltip="<?= $uiLang === 'cs' ? 'Vrstvy a Struktura' : 'Layers' ?>">
                        <i class="fa fa-bars text-sm"></i>
                    </button>
                </div>

                <div id="tab-content-blocks" class="tab-content active">
                    <div class="p-3 text-[10px] uppercase font-bold tracking-wider text-indigo-400 bg-black/40 border-b border-white/5 flex items-center justify-between">
                        <span><?= $uiLang === 'cs' ? 'Knihovna Bloků' : 'Block Library' ?></span>
                    </div>
                    <div id="blocks-container" class="flex-1 overflow-y-auto overflow-x-hidden p-2"></div>
                </div>

                <div id="tab-content-styles" class="tab-content">
                    <div class="p-3 text-[10px] uppercase font-bold tracking-wider text-indigo-400 bg-black/40 border-b border-white/5 flex items-center justify-between">
                        <span><?= $uiLang === 'cs' ? 'Styly a Vzhled' : 'Styles & Design' ?></span>
                    </div>
                    <div id="styles-container" class="flex-1 overflow-y-auto overflow-x-hidden p-2"></div>
                </div>

                <div id="tab-content-traits" class="tab-content">
                    <div class="p-3 text-[10px] uppercase font-bold tracking-wider text-indigo-400 bg-black/40 border-b border-white/5 flex items-center justify-between">
                        <span><?= $uiLang === 'cs' ? 'Nastavení Prvku' : 'Component Settings' ?></span>
                    </div>
                    <div id="traits-container" class="flex-1 overflow-y-auto overflow-x-hidden p-2"></div>
                </div>

                <div id="tab-content-layers" class="tab-content">
                    <div class="p-3 text-[10px] uppercase font-bold tracking-wider text-indigo-400 bg-black/40 border-b border-white/5 flex items-center justify-between">
                        <span><?= $uiLang === 'cs' ? 'Struktura Vrstev' : 'Layer Structure' ?></span>
                    </div>
                    <div id="layers-container" class="flex-1 overflow-y-auto overflow-x-hidden p-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.INITIAL_CONTENT = <?php echo json_encode($content); ?>;
        window.INITIAL_BODY_CLASS = <?php echo json_encode($initialBodyClass); ?>;
        window.UI_LANG = <?php echo json_encode($uiLang); ?>;
        <?php 
        require_once ROOT_DIR . 'admin/includes/CMS.php';
        $meta = CMS::getPageMeta($currentPage);
        ?>
        window.PAGE_META = <?php echo json_encode($meta); ?>;
        window.SITE_CONFIG = <?php echo json_encode(CMS::getSiteConfig()); ?>;
        window.ORIGINAL_TOP_PHP = <?php echo json_encode($originalTopPhp); ?>;

        function switchRightTab(tabName) {
            const tabs = ['styles', 'traits', 'layers', 'blocks'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                if (btn) btn.classList.toggle('active', t === tabName);
                if (content) content.classList.toggle('active', t === tabName);
            });
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-xs font-semibold text-white border transition-all duration-300 transform translate-y-2 opacity-0';

            let icon = 'fa-check-circle';
            if (type === 'success') {
                toast.classList.add('bg-emerald-950/90', 'border-emerald-500/40', 'text-emerald-200');
                icon = 'fa-check-circle';
            } else if (type === 'error') {
                toast.classList.add('bg-red-950/90', 'border-red-500/40', 'text-red-200');
                icon = 'fa-exclamation-circle';
            } else if (type === 'warning') {
                toast.classList.add('bg-amber-950/90', 'border-amber-500/40', 'text-amber-200');
                icon = 'fa-exclamation-triangle';
            } else {
                toast.classList.add('bg-slate-900/90', 'border-indigo-500/40', 'text-indigo-200');
                icon = 'fa-info-circle';
            }

            toast.innerHTML = `<i class="fa ${icon} text-sm shrink-0"></i><span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function showConfirmModal({ title, message, confirmText = 'Potvrdit', isDanger = true, icon = 'fa-exclamation-triangle', onConfirm }) {
            const modal = document.getElementById('confirm-modal');
            if (!modal) return;

            document.getElementById('confirm-title').innerText = title || 'Potvrdit akci';
            document.getElementById('confirm-message').innerText = message || 'Opravdu chcete tuto akci provést?';
            
            const iconBox = document.getElementById('confirm-icon-box');
            const iconEl = document.getElementById('confirm-icon');
            const actionBtn = document.getElementById('confirm-action-btn');

            if (iconEl) iconEl.className = 'fa ' + icon;
            
            if (isDanger) {
                if (iconBox) iconBox.className = 'w-14 h-14 rounded-2xl bg-red-500/20 text-red-400 border border-red-500/30 flex items-center justify-center mx-auto text-2xl shadow-lg';
                if (actionBtn) actionBtn.className = 'bg-red-600 hover:bg-red-500 text-white font-black px-6 py-2.5 rounded-xl shadow-lg shadow-red-600/20 active:transform active:scale-[0.98] transition-all text-xs uppercase';
            } else {
                if (iconBox) iconBox.className = 'w-14 h-14 rounded-2xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center mx-auto text-2xl shadow-lg';
                if (actionBtn) actionBtn.className = 'bg-indigo-600 hover:bg-indigo-500 text-white font-black px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 active:transform active:scale-[0.98] transition-all text-xs uppercase';
            }

            if (actionBtn) {
                actionBtn.innerText = confirmText;
                actionBtn.onclick = () => {
                    closeConfirmModal();
                    if (typeof onConfirm === 'function') onConfirm();
                };
            }

            modal.classList.remove('hidden');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirm-modal');
            if (modal) modal.classList.add('hidden');
        }

        function openPageSettings() {
            const modal = document.getElementById('settings-modal');
            if (modal) {
                const meta = window.PAGE_META || {};
                const slugInput = document.getElementById('meta-slug');
                const titleInput = document.getElementById('meta-title');
                const descInput = document.getElementById('meta-description');
                const kwInput = document.getElementById('meta-keywords');

                if (slugInput) slugInput.value = meta.slug || '';
                if (titleInput) titleInput.value = meta.title || '';
                if (descInput) descInput.value = meta.description || '';
                if (kwInput) kwInput.value = meta.keywords || '';

                modal.classList.remove('hidden');
            }
        }

        function closePageSettings() {
            const modal = document.getElementById('settings-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function savePageSettings() {
            const meta = window.PAGE_META || {};
            const slugInput = document.getElementById('meta-slug');
            const titleInput = document.getElementById('meta-title');
            const descInput = document.getElementById('meta-description');
            const kwInput = document.getElementById('meta-keywords');

            window.PAGE_META = {
                slug: slugInput ? slugInput.value.trim() : (meta.slug || ''),
                title: titleInput ? titleInput.value.trim() : (meta.title || ''),
                description: descInput ? descInput.value.trim() : (meta.description || ''),
                keywords: kwInput ? kwInput.value.trim() : (meta.keywords || '')
            };

            closePageSettings();
            
            const msg = document.getElementById('status-msg');
            if (msg) {
                msg.innerText = window.UI_LANG === 'en' ? 'Settings updated! Save page to apply.' : 'Nastavení aktualizováno! Uložte stránku.';
                msg.style.opacity = '1';
                setTimeout(() => msg.style.opacity = '0', 3500);
            }
        }

        let fmFiles = [];
        let fmCurrentFilter = 'all';
        let fmCurrentViewMode = 'grid';

        let pluginList = [];
        let pluginCurrentFilter = 'all';

        function switchView(view) {
            const editorBtn = document.getElementById('menu-btn-editor');
            const settingsBtn = document.getElementById('menu-btn-settings');
            const filesBtn = document.getElementById('menu-btn-files');
            const pluginsBtn = document.getElementById('menu-btn-plugins');
            const themesBtn = document.getElementById('menu-btn-themes');
            const gjsContainer = document.querySelector('.editor-canvas');
            const rightPanel = document.querySelector('.panel-right');
            const settingsPage = document.getElementById('settings-page-wrapper');
            const filesPage = document.getElementById('files-page-wrapper');
            const pluginsPage = document.getElementById('plugins-page-wrapper');
            const themesPage = document.getElementById('themes-page-wrapper');
            const headerPageControls = document.querySelector('header .flex.items-center.gap-4');
            const headerPageSettingsBtn = document.querySelector('header button[onclick="openPageSettings()"]');
            const headerSaveBtn = document.getElementById('save-btn');
            const headerStatusMsg = document.getElementById('status-msg');
            
            [editorBtn, settingsBtn, filesBtn, pluginsBtn, themesBtn].forEach(btn => {
                if (!btn) return;
                btn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors text-left";
                const icon = btn.querySelector('i');
                let iconClass = "fa-files-o";
                if (btn === settingsBtn) iconClass = "fa-globe";
                if (btn === filesBtn) iconClass = "fa-folder-open";
                if (btn === pluginsBtn) iconClass = "fa-plug";
                if (btn === themesBtn) iconClass = "fa-paint-brush";
                if (icon) icon.className = "fa " + iconClass + " w-4 text-center text-indigo-400";
            });

            if (view === 'editor') {
                if (window.EDIT_MODE !== 'theme_header' && window.EDIT_MODE !== 'theme_footer') {
                    window.EDIT_MODE = 'page';
                }
                editorBtn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10";
                editorBtn.querySelector('i').className = "fa fa-files-o w-4 text-center text-white";
                
                gjsContainer.classList.remove('hidden');
                rightPanel.classList.remove('hidden');
                settingsPage.classList.add('hidden');
                filesPage.classList.add('hidden');
                pluginsPage.classList.add('hidden');
                if (themesPage) themesPage.classList.add('hidden');
                
                if (headerPageControls) headerPageControls.classList.remove('invisible');
                if (headerPageSettingsBtn) headerPageSettingsBtn.classList.remove('hidden');
                if (headerSaveBtn) headerSaveBtn.classList.remove('hidden');
            } else if (view === 'settings') {
                settingsBtn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10";
                settingsBtn.querySelector('i').className = "fa fa-globe w-4 text-center text-white";
                
                gjsContainer.classList.add('hidden');
                rightPanel.classList.add('hidden');
                settingsPage.classList.remove('hidden');
                filesPage.classList.add('hidden');
                pluginsPage.classList.add('hidden');
                if (themesPage) themesPage.classList.add('hidden');
                
                if (headerPageControls) headerPageControls.classList.add('invisible');
                if (headerPageSettingsBtn) headerPageSettingsBtn.classList.add('hidden');
                if (headerSaveBtn) headerSaveBtn.classList.add('hidden');
                if (headerStatusMsg) headerStatusMsg.style.opacity = '0';
                
                const cfg = window.SITE_CONFIG || {};
                const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                setVal('site-name', cfg.site_name);
                const faviconVal = cfg.favicon || '';
                const faviconInput = document.getElementById('site-favicon');
                const faviconPreview = document.getElementById('site-favicon-preview');
                if (faviconInput) faviconInput.value = faviconVal;
                if (faviconPreview) {
                    if (faviconVal) {
                        faviconPreview.src = (faviconVal.startsWith('http') || faviconVal.startsWith('data:')) ? faviconVal : '../' + faviconVal;
                        faviconPreview.style.display = 'block';
                    } else {
                        faviconPreview.style.display = 'none';
                    }
                }
                setVal('site-phone-nonstop', cfg.phone_nonstop);
                setVal('site-phone-landline', cfg.phone_landline);
                setVal('site-email', cfg.email);
                setVal('site-address-headquarters', cfg.address_headquarters);
                setVal('site-address-dispatch', cfg.address_dispatch);
                setVal('site-ga-id', cfg.ga_id);
                setVal('site-contact-form-recipient', cfg.contact_form_recipient);
                setVal('site-404', cfg.error_page_404);
                const siteCacheEl = document.getElementById('site-enable-cache');
                if (siteCacheEl) siteCacheEl.checked = cfg.enable_cache || false;
                
                switchSettingsTab('general');
            } else if (view === 'files') {
                filesBtn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10";
                filesBtn.querySelector('i').className = "fa fa-folder-open w-4 text-center text-white";
                
                gjsContainer.classList.add('hidden');
                rightPanel.classList.add('hidden');
                settingsPage.classList.add('hidden');
                filesPage.classList.remove('hidden');
                pluginsPage.classList.add('hidden');
                if (themesPage) themesPage.classList.add('hidden');
                
                if (headerPageControls) headerPageControls.classList.add('invisible');
                if (headerPageSettingsBtn) headerPageSettingsBtn.classList.add('hidden');
                if (headerSaveBtn) headerSaveBtn.classList.add('hidden');
                if (headerStatusMsg) headerStatusMsg.style.opacity = '0';
                
                loadFileManagerFiles();
            } else if (view === 'plugins') {
                pluginsBtn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10";
                pluginsBtn.querySelector('i').className = "fa fa-plug w-4 text-center text-white";
                
                gjsContainer.classList.add('hidden');
                rightPanel.classList.add('hidden');
                settingsPage.classList.add('hidden');
                filesPage.classList.add('hidden');
                pluginsPage.classList.remove('hidden');
                if (themesPage) themesPage.classList.add('hidden');
                
                if (headerPageControls) headerPageControls.classList.add('invisible');
                if (headerPageSettingsBtn) headerPageSettingsBtn.classList.add('hidden');
                if (headerSaveBtn) headerSaveBtn.classList.add('hidden');
                if (headerStatusMsg) headerStatusMsg.style.opacity = '0';
                
                loadPlugins();
            } else if (view === 'themes') {
                if (themesBtn) {
                    themesBtn.className = "w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg transition-colors text-left shadow-md shadow-indigo-600/10";
                    themesBtn.querySelector('i').className = "fa fa-paint-brush w-4 text-center text-white";
                }
                
                gjsContainer.classList.add('hidden');
                rightPanel.classList.add('hidden');
                settingsPage.classList.add('hidden');
                filesPage.classList.add('hidden');
                pluginsPage.classList.add('hidden');
                if (themesPage) themesPage.classList.remove('hidden');
                
                if (headerPageControls) headerPageControls.classList.add('invisible');
                if (headerPageSettingsBtn) headerPageSettingsBtn.classList.add('hidden');
                if (headerSaveBtn) headerSaveBtn.classList.add('hidden');
                if (headerStatusMsg) headerStatusMsg.style.opacity = '0';
                
                loadThemes();
            }
        }

        function loadPlugins() {
            const container = document.getElementById('plugins-container');
            if (container) container.innerHTML = '<div class="text-center py-12 text-slate-500"><i class="fa fa-circle-o-notch fa-spin text-2xl mb-3"></i><p class="text-xs">Načítám nainstalované pluginy...</p></div>';

            fetch('plugins.php?action=list')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        pluginList = data.plugins || [];
                        const badge = document.getElementById('plugins-count-badge');
                        if (badge) badge.innerText = `${data.active_count} aktivních / ${data.total_count} celkem`;

                        renderPlugins();
                    } else {
                        alert('Chyba při načítání pluginů: ' + data.message);
                    }
                })
                .catch(err => {
                    if (container) container.innerHTML = '<div class="text-center py-12 text-red-400"><i class="fa fa-exclamation-triangle text-2xl mb-2"></i><p class="text-xs">Chyba při komunikaci se serverem.</p></div>';
                });
        }

        function setPluginFilter(filter) {
            pluginCurrentFilter = filter;
            document.querySelectorAll('.plugin-filter-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow');
                btn.classList.add('text-slate-400');
            });
            const activeBtn = document.getElementById('plugin-tab-' + filter);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-400');
                activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow');
            }
            renderPlugins();
        }

        function renderPlugins() {
            const container = document.getElementById('plugins-container');
            if (!container) return;

            const query = (document.getElementById('plugin-search-input')?.value || '').toLowerCase().trim();

            let filtered = pluginList.filter(plugin => {
                if (pluginCurrentFilter === 'active' && !plugin.active) return false;
                if (pluginCurrentFilter === 'inactive' && plugin.active) return false;
                if (query && !plugin.name.toLowerCase().includes(query) && !plugin.description.toLowerCase().includes(query)) return false;
                return true;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16 bg-slate-900/50 border border-white/5 rounded-2xl">
                        <i class="fa fa-plug text-4xl text-slate-600 mb-3"></i>
                        <h4 class="text-sm font-bold text-slate-300">Žádné pluginy nebyly nalezeny</h4>
                        <p class="text-xs text-slate-500 mt-1">Nahrajte váš první PHP plugin tlačítkem výše.</p>
                    </div>`;
                return;
            }

            let html = '';
            filtered.forEach(plugin => {
                const isActive = plugin.active;
                html += `
                    <div class="bg-slate-900 border ${isActive ? 'border-indigo-500/40 shadow-indigo-500/5' : 'border-white/5'} rounded-2xl p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-all">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-12 h-12 rounded-2xl ${isActive ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'bg-slate-950 text-slate-500'} flex items-center justify-center text-xl shrink-0 mt-0.5">
                                <i class="fa fa-plug"></i>
                            </div>
                            <div class="space-y-1 flex-1">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h3 class="text-base font-bold text-white tracking-tight">${plugin.name}</h3>
                                    <span class="text-[10px] bg-slate-950 text-indigo-300 border border-indigo-500/20 px-2 py-0.5 rounded-full font-mono font-semibold">v${plugin.version}</span>
                                    ${isActive ? '<span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Aktivní</span>' : '<span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Neaktivní</span>'}
                                    <button onclick="openPluginHelpModal('${plugin.id}')" class="w-6 h-6 rounded-lg bg-indigo-600/20 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-300 hover:text-white flex items-center justify-center transition-all text-xs" title="Jak použít tento plugin">
                                        <i class="fa fa-question"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-400 leading-relaxed">${plugin.description}</p>
                                <div class="text-[10px] text-slate-500 font-mono">Autor: <span class="text-slate-400">${plugin.author}</span> | Soubor: <span class="text-slate-400">${plugin.id}</span></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-between md:justify-end pt-4 md:pt-0 border-t md:border-t-0 border-white/5">
                            ${plugin.id.includes('smtp') ? '<button onclick="openSMTPModal()" class="bg-indigo-600/20 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-300 hover:text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"><i class="fa fa-cog"></i> Nastavení SMTP</button>' : ''}

                            <!-- Activation Toggle -->
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <span class="text-xs font-bold ${isActive ? 'text-indigo-300' : 'text-slate-400'}">${isActive ? 'Aktivováno' : 'Aktivovat'}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" ${isActive ? 'checked' : ''} onchange="togglePluginStatus('${plugin.id}', this.checked)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-950 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white"></div>
                                </div>
                            </label>

                            <button onclick="deletePlugin('${plugin.id}')" class="bg-red-950/30 hover:bg-red-600 text-red-300 hover:text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5" title="Smazat plugin">
                                <i class="fa fa-trash"></i> Smazat
                            </button>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        function openPluginHelpModal(pluginId) {
            const plugin = pluginList.find(p => p.id === pluginId) || { name: 'Plugin', description: '' };
            const modal = document.getElementById('plugin-help-modal');
            const titleEl = document.getElementById('plugin-help-title');
            const subtitleEl = document.getElementById('plugin-help-subtitle');
            const contentEl = document.getElementById('plugin-help-content');

            if (!modal || !contentEl) return;

            titleEl.innerText = `Nápověda: ${plugin.name}`;
            subtitleEl.innerText = `Jak nastavit a používat plugin "${plugin.name}" v projektu`;

            if (pluginId.includes('smtp')) {
                contentEl.innerHTML = `
                    <div class="space-y-4">
                        <div class="bg-indigo-950/40 border border-indigo-500/30 rounded-xl p-4">
                            <h4 class="text-sm font-bold text-indigo-300 mb-1 flex items-center gap-2">
                                <i class="fa fa-rocket"></i> 1. Odesílání e-mailů přes CMS::sendMail
                            </h4>
                            <p class="text-slate-300 mb-2">V jakémkoliv PHP skriptu webu (např. ve formuláři <code class="text-indigo-300 font-mono">send.php</code>) jednoduše zavolejte statickou metodu CMS:</p>
                            <pre class="bg-slate-950 p-3 rounded-lg border border-white/10 font-mono text-[11px] text-emerald-400 overflow-x-auto">CMS::sendMail($to, $subject, $body, $headers);</pre>
                            <p class="text-[11px] text-slate-400 mt-2">Pokud je plugin SMTP Mailer **aktivní**, e-mail proběhne přes nastavený SMTP server. Není třeba načítat PHPMailer ani nastavovat přihlašovací údaje přímo v kódu formuláře.</p>
                        </div>

                        <div class="bg-slate-950 border border-white/10 rounded-xl p-4">
                            <h4 class="text-sm font-bold text-white mb-2 flex items-center gap-2">
                                <i class="fa fa-code text-indigo-400"></i> 2. Příklad v send.php
                            </h4>
                            <pre class="bg-slate-900 p-3 rounded-lg border border-white/5 font-mono text-[11px] text-indigo-200 overflow-x-auto">
&lt;?php
require_once __DIR__ . '/admin/includes/CMS.php';

$recipient = "knez@fidamedia.cz";
$subject = "Nová zpráva z kontaktního formuláře";
$body = "Jméno: Jan Novák\nEmail: jan@example.cz\nText: Dobrý den!";
$headers = "Reply-To: jan@example.cz\r\n";

// Automaticky využije konfiguraci SMTP pluginu z administrace
$sent = CMS::sendMail($recipient, $subject, $body, $headers);

if ($sent) {
    echo json_encode(["success" => true, "message" => "E-mail úspěšně odeslán"]);
} else {
    echo json_encode(["success" => false, "message" => "Chyba při odesílání"]);
}
</pre>
                        </div>

                        <div class="bg-slate-950 border border-white/10 rounded-xl p-4 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <i class="fa fa-cogs text-indigo-400"></i> 3. Konfigurace v Administraci
                            </h4>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-300 text-xs">
                                <li>Klikněte na tlačítko <strong class="text-indigo-300"><i class="fa fa-cog"></i> Nastavení SMTP</strong> u pluginu.</li>
                                <li>Zadejte údaje vašeho poštovního serveru (např. Seznam: <code class="text-slate-400">smtp.seznam.cz</code>, port <code class="text-slate-400">587</code>, šifrování <code class="text-slate-400">TLS</code>).</li>
                                <li>Vložte uživatelské jméno a heslo k vaší e-mailové schránce.</li>
                                <li>Pro ověření stiskněte tlačítko <strong class="text-indigo-300">Otestovat spojení</strong>.</li>
                            </ul>
                        </div>
                    </div>
                `;
            } else {
                contentEl.innerHTML = `
                    <div class="space-y-4">
                        <div class="bg-slate-950 border border-white/10 rounded-xl p-4">
                            <h4 class="text-sm font-bold text-white mb-2 flex items-center gap-2">
                                <i class="fa fa-info-circle text-indigo-400"></i> Informace o pluginu
                            </h4>
                            <p class="text-slate-300 mb-3 leading-relaxed">${plugin.description || 'Pro tento plugin zatím není k dispozici detailnější popis.'}</p>
                            <div class="text-[11px] text-slate-400 space-y-1 font-mono border-t border-white/5 pt-2">
                                <div>Autor: <span class="text-white">${plugin.author || 'Neznámý autor'}</span></div>
                                <div>Verze: <span class="text-white">v${plugin.version || '1.0.0'}</span></div>
                                <div>Soubor: <span class="text-white">${plugin.id}</span></div>
                            </div>
                        </div>

                        <div class="bg-indigo-950/40 border border-indigo-500/30 rounded-xl p-4">
                            <h4 class="text-sm font-bold text-indigo-300 mb-1 flex items-center gap-2">
                                <i class="fa fa-check-circle"></i> Jak funguje aktivace
                            </h4>
                            <p class="text-slate-300 leading-relaxed">Při zapnutí přepínače <strong>Aktivovat</strong> se skript pluginu automaticky načítá při každém požadavku na web přes <code class="text-indigo-300">CMS::loadActivePlugins()</code>. Funkce a třídy definované v pluginu jsou ihned dostupné v celém systému.</p>
                        </div>
                    </div>
                `;
            }

            modal.classList.remove('hidden');
        }

        function closePluginHelpModal() {
            document.getElementById('plugin-help-modal')?.classList.add('hidden');
        }

        function openSMTPModal() {
            fetch('plugins.php?action=get_smtp_config')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const cfg = data.config || {};
                        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                        setVal('smtp-host', cfg.host);
                        setVal('smtp-port', cfg.port || 587);
                        setVal('smtp-encryption', cfg.encryption || 'tls');
                        setVal('smtp-username', cfg.username);
                        setVal('smtp-password', cfg.password);
                        setVal('smtp-from-email', cfg.from_email);
                        setVal('smtp-from-name', cfg.from_name || 'Elektroservis Plzeň');

                        document.getElementById('smtp-modal')?.classList.remove('hidden');
                    } else {
                        alert(data.message || 'Nepodařilo se načíst konfiguraci SMTP.');
                    }
                });
        }

        function closeSMTPModal() {
            document.getElementById('smtp-modal')?.classList.add('hidden');
        }

        function saveSMTPConfig() {
            const getVal = (id) => { const el = document.getElementById(id); return el ? el.value.trim() : ''; };
            const data = {
                host: getVal('smtp-host'),
                port: getVal('smtp-port'),
                encryption: getVal('smtp-encryption'),
                username: getVal('smtp-username'),
                password: getVal('smtp-password'),
                from_email: getVal('smtp-from-email'),
                from_name: getVal('smtp-from-name')
            };

            const btn = document.getElementById('btn-save-smtp');
            const originalText = btn.innerText;
            btn.innerText = 'UKLÁDÁM...';
            btn.disabled = true;

            fetch('plugins.php?action=save_smtp_config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(resData => {
                alert(resData.message);
                if (resData.status === 'success') {
                    closeSMTPModal();
                }
            })
            .catch(err => {
                alert('Chyba při ukládání nastavení SMTP.');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }

        function testSMTPConnection() {
            const testEmail = prompt('Zadejte cílovou e-mailovou adresu pro zaslání testovací zprávy:', document.getElementById('smtp-from-email').value || 'info@elektroservisplzen.cz');
            if (!testEmail) return;

            const btn = document.getElementById('btn-test-smtp');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ODESÍLÁM...';
            btn.disabled = true;

            fetch('plugins.php?action=test_smtp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ test_email: testEmail })
            })
            .then(res => res.json())
            .then(resData => {
                alert(resData.message);
            })
            .catch(err => {
                alert('Chyba při komunikaci se serverem.');
            })
            .finally(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }

        function togglePluginStatus(pluginId, activeState) {
            fetch('plugins.php?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ plugin_id: pluginId, active: activeState })
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        loadPlugins();
                    } else {
                        alert('Chyba: ' + (data.message || 'Nepodařilo se změnit stav.'));
                        loadPlugins();
                    }
                } catch(e) {
                    alert('Odpověď serveru: ' + text);
                    loadPlugins();
                }
            })
            .catch(err => {
                alert('Chyba při komunikaci se serverem.');
                loadPlugins();
            });
        }

        function handlePluginUpload(files) {
            if (!files || files.length === 0) return;

            const file = files[0];
            const formData = new FormData();
            formData.append('plugin_file', file);
            formData.append('action', 'upload');

            fetch('plugins.php?action=upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    loadPlugins();
                }
                const input = document.getElementById('plugin-file-input');
                if (input) input.value = '';
            })
            .catch(err => {
                alert('Chyba při nahrávání pluginu.');
            });
        }

        function deletePlugin(pluginId) {
            showConfirmModal({
                title: 'Smazat plugin',
                message: `Opravdu chcete smazat plugin "${pluginId}"?`,
                confirmText: 'Smazat plugin',
                isDanger: true,
                icon: 'fa-puzzle-piece',
                onConfirm: () => {
                    fetch('plugins.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ plugin_id: pluginId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('Plugin byl úspěšně smazán.', 'success');
                            loadPlugins();
                        } else {
                            showToast('Chyba: ' + data.message, 'error');
                        }
                    })
                    .catch(err => showToast('Chyba při mazání pluginu.', 'error'));
                }
            });
        }

        let themeList = [];

        function loadThemes() {
            const container = document.getElementById('themes-container');
            if (container) container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-500"><i class="fa fa-circle-o-notch fa-spin text-2xl mb-3"></i><p class="text-xs">Načítám témata...</p></div>';

            fetch('themes.php?action=list')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        themeList = data.themes || [];
                        const activeTheme = data.active_theme || {};
                        const badge = document.getElementById('active-theme-badge');
                        if (badge) badge.innerText = activeTheme.name || 'Default';

                        renderThemes();
                    } else {
                        alert('Chyba při načítání témat: ' + data.message);
                    }
                })
                .catch(err => {
                    if (container) container.innerHTML = '<div class="col-span-full text-center py-12 text-red-400"><i class="fa fa-exclamation-triangle text-2xl mb-2"></i><p class="text-xs">Chyba při komunikaci se serverem.</p></div>';
                });
        }

        function renderThemes() {
            const container = document.getElementById('themes-container');
            if (!container) return;

            if (themeList.length === 0) {
                container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-500">Žádné vzhledy nebyly nalezeny.</div>';
                return;
            }

            let html = '';
            themeList.forEach(t => {
                const isActive = t.active;
                const screenshotSrc = t.screenshot || 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250"><rect width="400" height="250" fill="%231e293b"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%2394a3b8" font-family="sans-serif" font-size="16">Fida Theme</text></svg>';

                html += `
                    <div class="bg-slate-900 border ${isActive ? 'border-indigo-500 shadow-indigo-500/10' : 'border-white/5'} rounded-2xl overflow-hidden flex flex-col justify-between transition-all hover:border-white/20 shadow-xl">
                        <div>
                            <div class="h-44 bg-slate-950 relative overflow-hidden flex items-center justify-center">
                                <img src="${screenshotSrc}" alt="${t.name}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'400\\' height=\\'250\\' viewBox=\\'0 0 400 250\\'><rect width=\\'400\\' height=\\'250\\' fill=\\'%231e293b\\'/><text x=\\'50%\\' y=\\'50%\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' fill=\\'%2394a3b8\\' font-family=\\'sans-serif\\' font-size=\\'16\\'>${t.name}</text></svg>'">
                                ${isActive ? '<div class="absolute top-3 right-3 bg-indigo-600 text-white text-[10px] uppercase font-black px-3 py-1 rounded-full shadow-lg">Aktivní</div>' : ''}
                            </div>
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <h3 class="font-bold text-white text-base truncate">${t.name}</h3>
                                    <span class="text-[10px] text-slate-400 font-mono bg-white/5 px-2 py-0.5 rounded">v${t.version}</span>
                                </div>
                                <p class="text-slate-400 text-xs line-clamp-2 mb-3">${t.description}</p>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1">
                                    <i class="fa fa-user-circle"></i> ${t.author}
                                </div>
                            </div>
                        </div>
                        <div class="p-5 border-t border-white/5 bg-slate-950/40">
                            ${isActive 
                                ? '<button disabled class="w-full bg-slate-800 text-slate-400 font-bold py-2.5 rounded-xl text-xs cursor-default">Aktivní Vzhled</button>'
                                : `<button onclick="activateTheme('${t.id}')" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition-all text-xs shadow-lg shadow-indigo-600/20">Aktivovat vzhled</button>`
                            }
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        function activateTheme(themeId) {
            fetch('themes.php?action=activate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme_id: themeId })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.reload();
                }
            })
            .catch(err => alert('Chyba při aktivaci vzhledu.'));
        }

        function handleThemeUpload(files) {
            if (!files || files.length === 0) return;

            const file = files[0];
            const formData = new FormData();
            formData.append('theme_file', file);

            fetch('themes.php?action=upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    loadThemes();
                }
                const input = document.getElementById('theme-file-input');
                if (input) input.value = '';
            })
            .catch(err => alert('Chyba při nahrávání vzhledu.'));
        }

        function editThemeHeader() {
            fetch('themes.php?action=get_header')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.EDIT_MODE = 'theme_header';
                        switchView('editor');
                        if (window.editor) {
                            window.editor.setComponents(data.content || '');
                        }
                    } else {
                        alert('Nepodařilo se načíst hlavičku: ' + data.message);
                    }
                })
                .catch(err => alert('Chyba při načítání hlavičky.'));
        }

        function editThemeFooter() {
            fetch('themes.php?action=get_footer')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.EDIT_MODE = 'theme_footer';
                        switchView('editor');
                        if (window.editor) {
                            window.editor.setComponents(data.content || '');
                        }
                    } else {
                        alert('Nepodařilo se načíst patičku: ' + data.message);
                    }
                })
                .catch(err => alert('Chyba při načítání patičky.'));
        }

        function loadFileManagerFiles() {
            const container = document.getElementById('fm-files-container');
            if (container) container.innerHTML = '<div class="text-center py-12 text-slate-500"><i class="fa fa-circle-o-notch fa-spin text-2xl mb-3"></i><p class="text-xs">Načítám seznam souborů...</p></div>';

            fetch('files.php?action=list')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        fmFiles = data.files || [];
                        const badge = document.getElementById('files-count-badge');
                        if (badge) badge.innerText = `${fmFiles.length} souborů`;
                        
                        const stats = document.getElementById('fm-stats-text');
                        if (stats) stats.innerText = `Celkem: ${data.stats.count} souborů (${data.stats.total_size_formatted})`;
                        
                        renderFileManager();
                    } else {
                        alert('Chyba při načítání souborů: ' + data.message);
                    }
                })
                .catch(err => {
                    if (container) container.innerHTML = '<div class="text-center py-12 text-red-400"><i class="fa fa-exclamation-triangle text-2xl mb-2"></i><p class="text-xs">Chyba při komunikaci se serverem.</p></div>';
                });
        }

        function setFmFilter(filter) {
            fmCurrentFilter = filter;
            document.querySelectorAll('.fm-filter-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow');
                btn.classList.add('text-slate-400');
            });
            const activeBtn = document.getElementById('fm-tab-' + filter);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-400');
                activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow');
            }
            renderFileManager();
        }

        function setFmViewMode(mode) {
            fmCurrentViewMode = mode;
            const gridBtn = document.getElementById('fm-view-grid-btn');
            const listBtn = document.getElementById('fm-view-list-btn');
            if (mode === 'grid') {
                gridBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white transition-all';
                listBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-white transition-all';
            } else {
                listBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white transition-all';
                gridBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-white transition-all';
            }
            renderFileManager();
        }

        function renderFileManager() {
            const container = document.getElementById('fm-files-container');
            if (!container) return;

            const query = (document.getElementById('fm-search-input')?.value || '').toLowerCase().trim();

            let filtered = fmFiles.filter(file => {
                if (fmCurrentFilter !== 'all' && file.type !== fmCurrentFilter) return false;
                if (query && !file.name.toLowerCase().includes(query)) return false;
                return true;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16 bg-slate-900/50 border border-white/5 rounded-2xl">
                        <i class="fa fa-folder-open-o text-4xl text-slate-600 mb-3"></i>
                        <h4 class="text-sm font-bold text-slate-300">Žádné soubory nebyly nalezeny</h4>
                        <p class="text-xs text-slate-500 mt-1">Zkuste změnit filtr vyhledávání nebo nahrajte nové soubory.</p>
                    </div>`;
                return;
            }

            if (fmCurrentViewMode === 'grid') {
                let html = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">';
                filtered.forEach(file => {
                    let thumbHtml = '';
                    if (file.type === 'image') {
                        thumbHtml = `<img src="../${file.url}" alt="${file.name}" class="w-full h-full object-cover rounded-xl transition-transform duration-300 group-hover:scale-105" loading="lazy">`;
                    } else {
                        let iconClass = 'fa-file-o';
                        if (file.ext === 'pdf') iconClass = 'fa-file-pdf-o text-red-400';
                        else if (['doc', 'docx'].includes(file.ext)) iconClass = 'fa-file-word-o text-blue-400';
                        else if (['xls', 'xlsx', 'csv'].includes(file.ext)) iconClass = 'fa-file-excel-o text-emerald-400';
                        else if (['zip', 'rar'].includes(file.ext)) iconClass = 'fa-file-archive-o text-amber-400';
                        else if (['mp4', 'avi', 'mov'].includes(file.ext)) iconClass = 'fa-file-video-o text-purple-400';
                        else if (['mp3', 'wav'].includes(file.ext)) iconClass = 'fa-file-audio-o text-pink-400';

                        thumbHtml = `<div class="w-full h-full flex flex-col items-center justify-center bg-slate-950/60 rounded-xl"><i class="fa ${iconClass} text-3xl mb-1"></i><span class="text-[9px] uppercase font-bold text-slate-500">${file.ext}</span></div>`;
                    }

                    html += `
                        <div class="bg-slate-900 border border-white/5 hover:border-indigo-500/40 rounded-2xl p-3 flex flex-col justify-between transition-all hover:shadow-xl group relative overflow-hidden">
                            <div class="h-32 w-full mb-2 overflow-hidden rounded-xl bg-slate-950 relative cursor-pointer" onclick="openFmPreviewModal('${file.name}')">
                                ${thumbHtml}
                                <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button title="Náhled" class="w-8 h-8 rounded-lg bg-slate-900/90 text-white hover:bg-indigo-600 flex items-center justify-center text-xs transition-colors"><i class="fa fa-eye"></i></button>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-200 truncate" title="${file.name}">${file.name}</div>
                                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1 font-mono">
                                    <span>${file.size_formatted}</span>
                                    <span>${file.dimensions || file.ext.toUpperCase()}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-white/5">
                                <button onclick="copyFmUrl('${file.url}', this)" class="flex-1 bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white py-1.5 px-2 rounded-lg text-[10px] font-bold transition-all flex items-center justify-center gap-1" title="Kopírovat cestu k souboru">
                                    <i class="fa fa-copy"></i> Kopírovat
                                </button>
                                <button onclick="deleteFmFile('${file.name}')" class="bg-red-950/40 hover:bg-red-600 text-red-300 hover:text-white p-1.5 rounded-lg text-[10px] transition-all" title="Smazat soubor">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                let html = `
                    <div class="bg-slate-900 border border-white/5 rounded-2xl overflow-hidden shadow-xl">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950/70 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-white/5">
                                <tr>
                                    <th class="p-3.5 pl-5">Soubor</th>
                                    <th class="p-3.5">Typ / Rozměry</th>
                                    <th class="p-3.5">Velikost</th>
                                    <th class="p-3.5">Datum nahrání</th>
                                    <th class="p-3.5 text-right pr-5">Akce</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">`;
                
                filtered.forEach(file => {
                    html += `
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="p-3.5 pl-5 font-medium text-white flex items-center gap-3">
                                <i class="fa ${file.type === 'image' ? 'fa-file-image-o text-indigo-400' : 'fa-file-o text-slate-400'} text-base"></i>
                                <span class="truncate max-w-xs cursor-pointer hover:text-indigo-400 transition-colors" onclick="openFmPreviewModal('${file.name}')">${file.name}</span>
                            </td>
                            <td class="p-3.5 text-slate-400 font-mono">${file.dimensions || file.ext.toUpperCase()}</td>
                            <td class="p-3.5 text-slate-400 font-mono">${file.size_formatted}</td>
                            <td class="p-3.5 text-slate-400">${file.mtime_formatted}</td>
                            <td class="p-3.5 pr-5 text-right space-x-2">
                                <button onclick="copyFmUrl('${file.url}', this)" class="bg-slate-800 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all">
                                    <i class="fa fa-copy mr-1"></i> Kopírovat URL
                                </button>
                                <button onclick="deleteFmFile('${file.url}')" class="bg-red-950/40 hover:bg-red-600 text-red-300 hover:text-white px-2.5 py-1.5 rounded-lg text-[10px] transition-all">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                html += `</tbody></table></div>`;
                container.innerHTML = html;
            }
        }

        function copyFmUrl(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check text-emerald-400"></i> Zkopírováno!';
                setTimeout(() => { btn.innerHTML = originalText; }, 2000);
            }).catch(() => {
                prompt("Cesta k souboru (CTRL+C pro zkopírování):", url);
            });
        }

        function handleFileUpload(files) {
            if (!files || files.length === 0) return;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            formData.append('action', 'upload');

            const progressBox = document.getElementById('fm-upload-progress');
            const progressBar = document.getElementById('fm-upload-bar');
            const percentText = document.getElementById('fm-upload-percent-text');

            if (progressBox) progressBox.classList.remove('hidden');
            if (progressBar) progressBar.style.width = '0%';
            if (percentText) percentText.innerText = '0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'files.php?action=upload', true);

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    if (progressBar) progressBar.style.width = percent + '%';
                    if (percentText) percentText.innerText = percent + '%';
                }
            };

            xhr.onload = () => {
                if (progressBox) progressBox.classList.add('hidden');
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.status === 'success') {
                            loadFileManagerFiles();
                        } else {
                            alert('Chyba: ' + data.message);
                        }
                    } catch(e) {
                        loadFileManagerFiles();
                    }
                } else {
                    alert('Chyba při nahrávání souborů.');
                }
                const input = document.getElementById('fm-file-input');
                if (input) input.value = '';
            };

            xhr.onerror = () => {
                if (progressBox) progressBox.classList.add('hidden');
                alert('Chyba při nahrávání (připojení selhalo).');
            };

            xhr.send(formData);
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('fm-dropzone')?.classList.add('border-indigo-500', 'bg-indigo-950/30');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('fm-dropzone')?.classList.remove('border-indigo-500', 'bg-indigo-950/30');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('fm-dropzone')?.classList.remove('border-indigo-500', 'bg-indigo-950/30');
            if (e.dataTransfer && e.dataTransfer.files) {
                handleFileUpload(e.dataTransfer.files);
            }
        }

        function deleteFmFile(filename) {
            showConfirmModal({
                title: 'Smazat soubor',
                message: `Opravdu chcete smazat soubor "${filename}"?`,
                confirmText: 'Smazat soubor',
                isDanger: true,
                icon: 'fa-trash',
                onConfirm: () => {
                    fetch('files.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ filename: filename })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast('Soubor byl smazán.', 'success');
                            loadFileManagerFiles();
                        } else {
                            showToast('Chyba: ' + data.message, 'error');
                        }
                    })
                    .catch(err => showToast('Chyba při mazání souboru.', 'error'));
                }
            });
        }

        function openFmPreviewModal(filename) {
            const file = fmFiles.find(f => f.name === filename);
            if (!file) return;

            document.getElementById('fm-preview-title').innerText = file.name;
            const content = document.getElementById('fm-preview-content');
            
            if (file.type === 'image') {
                content.innerHTML = `<img src="../${file.url}" alt="${file.name}" class="max-h-[60vh] max-w-full object-contain rounded-xl shadow-2xl border border-white/10">`;
            } else {
                content.innerHTML = `<div class="text-center py-12"><i class="fa fa-file-text-o text-6xl text-indigo-400 mb-4"></i><p class="text-sm font-bold text-white">${file.name}</p></div>`;
            }

            document.getElementById('fm-preview-meta').innerText = `Velikost: ${file.size_formatted} | Datum: ${file.mtime_formatted}${file.dimensions ? ' | Rozměry: ' + file.dimensions : ''}`;
            
            const copyBtn = document.getElementById('fm-preview-copy-btn');
            if (copyBtn) copyBtn.onclick = () => copyFmUrl(file.url, copyBtn);

            const openLink = document.getElementById('fm-preview-open-link');
            if (openLink) openLink.href = '../' + file.url;

            document.getElementById('fm-preview-modal')?.classList.remove('hidden');
        }

        function closeFmPreviewModal() {
            document.getElementById('fm-preview-modal')?.classList.add('hidden');
        }

        function switchSettingsTab(tabId) {
            document.getElementById('content-tab-general').classList.add('hidden');
            document.getElementById('content-tab-contacts').classList.add('hidden');
            document.getElementById('content-tab-addresses').classList.add('hidden');
            document.getElementById('content-tab-cache').classList.add('hidden');
            document.getElementById('content-tab-updates').classList.add('hidden');
            
            const targetTab = document.getElementById('content-tab-' + tabId);
            if (targetTab) targetTab.classList.remove('hidden');
            
            const tabButtons = document.querySelectorAll('.settings-tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-white');
                btn.classList.add('border-transparent', 'text-slate-400');
            });
            
            const activeBtn = document.getElementById('btn-tab-' + tabId);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-slate-400');
                activeBtn.classList.add('border-indigo-500', 'text-white');
            }
        }

        function uploadFaviconFile(input) {
            if (!input.files || !input.files[0]) return;
            const formData = new FormData();
            formData.append('favicon_file', input.files[0]);

            const labelBtn = input.parentElement;
            const originalHtml = labelBtn.innerHTML;
            labelBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Nahrávám...';

            fetch('settings.php?action=upload_favicon', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const faviconInput = document.getElementById('site-favicon');
                    const faviconPreview = document.getElementById('site-favicon-preview');
                    if (faviconInput) faviconInput.value = data.favicon;
                    if (faviconPreview) {
                        faviconPreview.src = '../' + data.favicon + '?t=' + Date.now();
                        faviconPreview.style.display = 'block';
                    }
                    if (window.SITE_CONFIG) window.SITE_CONFIG.favicon = data.favicon;
                    alert('Favicon byl úspěšně nahrán!');
                } else {
                    alert('Chyba při nahrávání: ' + data.message);
                }
            })
            .catch(err => {
                alert('Chyba při nahrávání souboru.');
            })
            .finally(() => {
                labelBtn.innerHTML = originalHtml;
            });
        }

        function saveGlobalSettings() {
            const getVal = (id) => { const el = document.getElementById(id); return el ? el.value : ''; };
            const data = {
                site_name: getVal('site-name'),
                favicon: getVal('site-favicon'),
                phone_nonstop: getVal('site-phone-nonstop'),
                phone_landline: getVal('site-phone-landline'),
                email: getVal('site-email'),
                address_headquarters: getVal('site-address-headquarters'),
                address_dispatch: getVal('site-address-dispatch'),
                ga_id: getVal('site-ga-id'),
                contact_form_recipient: getVal('site-contact-form-recipient'),
                error_page_404: getVal('site-404'),
                enable_cache: document.getElementById('site-enable-cache')?.checked || false
            };

            const btn = document.querySelector('#settings-page-wrapper button[onclick="saveGlobalSettings()"]');
            const originalText = btn.innerText;
            btn.innerText = 'UKLÁDÁM...';
            btn.disabled = true;

            fetch('settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    location.reload();
                }
            })
            .catch(err => {
                alert('Chyba při ukládání nastavení.');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }

        function rebuildCacheManual() {
            const btn = document.getElementById('rebuild-cache-btn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> GENERUJI...';
            btn.disabled = true;

            fetch('settings.php?action=rebuild_cache')
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(err => {
                    alert('Chyba při generování cache.');
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

        function checkUpdates() {
            fetch('update.php?action=check')
                .then(res => res.json())
                .then(data => {
                    if (data.updates_available) {
                        document.getElementById('update-banner').classList.remove('hidden');
                    }
                })
                .catch(err => {});
        }

        function checkUpdatesManual() {
            const btn = document.getElementById('btn-check-updates');
            const box = document.getElementById('update-status-box');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Kontroluji...';
            }

            fetch('update.php?action=check')
                .then(res => res.json())
                .then(data => {
                    if (box) {
                        box.className = 'p-4 rounded-xl text-sm font-medium border';
                        if (data.updates_available) {
                            box.classList.add('bg-amber-500/10', 'border-amber-500/20', 'text-amber-400');
                        } else if (data.status === 'success') {
                            box.classList.add('bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-400');
                        } else {
                            box.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-400');
                        }
                        box.innerHTML = data.message;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => alert('Chyba při kontrole aktualizací.'))
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-search"></i> Zkontrolovat aktualizace';
                    }
                });
        }

        function saveCmsRepoSettings() {
            const cmsRepoUrl = document.getElementById('cms-repo-url-input').value;
            const btn = document.getElementById('btn-save-cms-repo');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'UKLÁDÁM...';
            }

            fetch('update.php?action=save_cms_repo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cms_repo_url: cmsRepoUrl })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            })
            .catch(err => alert('Chyba při ukládání nastavení CMS repozitáře.'))
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'ULOŽIT REPOZITÁŘ JÁDRA CMS';
                }
            });
        }

        function saveProjectRepoSettings() {
            const projectRepoUrl = document.getElementById('project-repo-url-input').value;
            const githubToken = document.getElementById('github-token-input').value;
            const enableProjectGit = document.getElementById('enable-project-git-toggle').checked;
            const btn = document.getElementById('btn-save-project-repo');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'UKLÁDÁM...';
            }

            fetch('update.php?action=save_project_repo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    project_repo_url: projectRepoUrl,
                    github_token: githubToken,
                    enable_project_git: enableProjectGit
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            })
            .catch(err => alert('Chyba při ukládání nastavení projektového repozitáře.'))
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'ULOŽIT PROJEKTOVÝ REPOZITÁŘ';
                }
            });
        }

        function runUpdate() {
            showConfirmModal({
                title: 'Aktualizace systému',
                message: 'Opravdu chcete aktualizovat systém Fida CMS z GitHubu?',
                confirmText: 'Aktualizovat nyní',
                isDanger: false,
                icon: 'fa-refresh',
                onConfirm: () => {
                    const btn = document.querySelector('#update-banner button');
                    const originalHtml = btn ? btn.innerHTML : '';
                    if (btn) {
                        btn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> AKTUALIZUJI...';
                        btn.disabled = true;
                    }

                    fetch('update.php?action=pull')
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showToast(data.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('Chyba: ' + data.message, 'error');
                                if (btn) {
                                    btn.innerHTML = originalHtml;
                                    btn.disabled = false;
                                }
                            }
                        });
                }
            });
        }

        function createNewPage() {
            const filename = prompt("Zadejte název nové stránky (např. sluzby):");
            if (!filename) return;
            
            fetch('create_page.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filename: filename })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    if (data.redirect) setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    showToast('Chyba: ' + data.message, 'error');
                }
            });
        }

        setTimeout(checkUpdates, 2000);
        setInterval(checkUpdates, 60000);

        function deleteCurrentPage() {
            showConfirmModal({
                title: 'Smazat stránku',
                message: 'Opravdu chcete nenávratně smazat aktuální stránku? Tato akce nelze vrátit.',
                confirmText: 'Smazat stránku',
                isDanger: true,
                icon: 'fa-trash',
                onConfirm: () => {
                    fetch('delete.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ page: '<?= $currentPage ?>' })
                    })
                    .then(res => res.json())
                    .then(data => {
                        showToast(data.message, data.status === 'success' ? 'success' : 'error');
                        if (data.status === 'success') {
                            setTimeout(() => window.location.href = 'index.php', 1200);
                        }
                    });
                }
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-left');
            const icon = document.getElementById('sidebar-toggle-icon');
            const toggleText = document.querySelector('#sidebar-toggle-btn .sidebar-text');
            
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('fida_cms_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            
            if (isCollapsed) {
                icon.className = 'fa fa-angle-double-right';
                if (toggleText) toggleText.innerText = '';
            } else {
                icon.className = 'fa fa-angle-double-left';
                if (toggleText) toggleText.innerText = 'Zmenšit panel';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const isCollapsed = localStorage.getItem('fida_cms_sidebar_collapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar-left');
                const icon = document.getElementById('sidebar-toggle-icon');
                const toggleText = document.querySelector('#sidebar-toggle-btn .sidebar-text');
                
                sidebar.classList.add('collapsed');
                if (icon) icon.className = 'fa fa-angle-double-right';
                if (toggleText) toggleText.innerText = '';
            }

            // Fast Tooltips Conversion
            const convertTooltips = () => {
                document.querySelectorAll('[title]').forEach(el => {
                    const t = el.getAttribute('title');
                    if (t) {
                        el.setAttribute('data-tooltip', t);
                        el.removeAttribute('title');
                    }
                });
            };
            convertTooltips();
            setInterval(convertTooltips, 1000);
        });
    </script>
    <script src="js/editor.js"></script>
</body>
</html>
