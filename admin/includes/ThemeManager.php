<?php

class ThemeManager {
    private string $rootDir;
    private string $themesDir;
    private string $configFile;

    public function __construct(?string $rootDir = null) {
        if ($rootDir) {
            $this->rootDir = rtrim(realpath($rootDir) ?: $rootDir, '/');
        } else {
            $this->rootDir = rtrim(realpath(__DIR__ . '/../../') ?: realpath(__DIR__ . '/../') ?: __DIR__ . '/..', '/');
        }

        $this->themesDir = $this->rootDir . '/themes/';
        if (!file_exists($this->themesDir)) {
            @mkdir($this->themesDir, 0777, true);
        }

        $this->configFile = $this->rootDir . '/config/site.json';
    }

    /**
     * Dispatch HTTP action to corresponding handler.
     */
    public function handleRequest(string $action): array {
        switch ($action) {
            case 'list':
                return $this->handleList();
            case 'activate':
                return $this->handleActivate();
            case 'upload':
                return $this->handleUpload();
            case 'get_header':
                return ['status' => 'success', 'content' => $this->getHeaderContent()];
            case 'save_header':
                return $this->handleSaveHeader();
            case 'get_footer':
                return ['status' => 'success', 'content' => $this->getFooterContent()];
            case 'save_footer':
                return $this->handleSaveFooter();
            default:
                return ['status' => 'error', 'message' => 'Neplatná akce.'];
        }
    }

    public function getActiveThemeId(): string {
        if (file_exists($this->configFile)) {
            $json = @json_decode(@file_get_contents($this->configFile), true);
            if (is_array($json) && !empty($json['active_theme'])) {
                return $json['active_theme'];
            }
        }
        return 'default';
    }

    public function getActiveThemeBodyClass(): string {
        $header = $this->getHeaderContent();
        if (preg_match('/<body[^>]*class=["\']([^"\']*)["\']>/i', $header, $matches)) {
            return $matches[1];
        }
        return 'bg-slate-950 text-slate-100 min-h-screen';
    }

    public function getActiveTheme(): array {
        $activeId = $this->getActiveThemeId();
        $themes = $this->getInstalledThemes();
        foreach ($themes as $theme) {
            if ($theme['id'] === $activeId) {
                return $theme;
            }
        }
        return [
            'id' => $activeId,
            'name' => ucfirst($activeId),
            'version' => '1.0.0',
            'description' => 'Aktivní vzhled.',
            'author' => 'Neznámý autor',
            'active' => true
        ];
    }

    public function getInstalledThemes(): array {
        $activeId = $this->getActiveThemeId();
        $installed = [];

        if (!file_exists($this->themesDir)) {
            return [];
        }

        $items = @scandir($this->themesDir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $itemPath = $this->themesDir . $item;
            if (is_dir($itemPath)) {
                $jsonFile = $itemPath . '/theme.json';
                $meta = [];
                if (file_exists($jsonFile)) {
                    $meta = @json_decode(@file_get_contents($jsonFile), true) ?: [];
                }

                $screenshot = $meta['screenshot'] ?? '';
                if (empty($screenshot)) {
                    foreach (['screenshot.svg', 'screenshot.png', 'screenshot.jpg', 'screenshot.webp'] as $imgFile) {
                        if (file_exists($itemPath . '/' . $imgFile)) {
                            $screenshot = $imgFile;
                            break;
                        }
                    }
                }

                $screenshotUrl = '';
                if (!empty($screenshot)) {
                    if (strpos($screenshot, 'http://') === 0 || strpos($screenshot, 'https://') === 0 || strpos($screenshot, 'data:') === 0) {
                        $screenshotUrl = $screenshot;
                    } else {
                        $screenshotFilename = basename($screenshot);
                        if (file_exists($itemPath . '/' . $screenshotFilename)) {
                            $screenshotUrl = '../themes/' . $item . '/' . $screenshotFilename;
                        } else {
                            $screenshotUrl = '../' . ltrim($screenshot, '/');
                        }
                    }
                }

                $installed[] = [
                    'id' => $item,
                    'name' => $meta['name'] ?? ucfirst($item),
                    'version' => $meta['version'] ?? '1.0.0',
                    'description' => $meta['description'] ?? 'Bez popisu.',
                    'author' => $meta['author'] ?? 'Neznámý autor',
                    'screenshot' => $screenshotUrl,
                    'active' => ($item === $activeId)
                ];
            }
        }

        return $installed;
    }

    public function setActiveTheme(string $themeId): array {
        $themeFolder = $this->themesDir . $themeId;
        if (!is_dir($themeFolder)) {
            return ['status' => 'error', 'message' => 'Složka vzhledu neexistuje.'];
        }

        $site = [];
        if (file_exists($this->configFile)) {
            $site = @json_decode(@file_get_contents($this->configFile), true) ?: [];
        }

        $site['active_theme'] = $themeId;

        $json = json_encode($site, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($this->configFile, $json) !== false) {
            @chmod($this->configFile, 0666);
            CMS::generateCache();
            CMS::gitCommit("Switch active theme to: $themeId");

            return ['status' => 'success', 'message' => "Vzhled $themeId byl úspěšně aktivován."];
        }

        return ['status' => 'error', 'message' => 'Chyba při zápisu do site.json.'];
    }

    public function getHeaderContent(?string $themeId = null): string {
        $themeId = $themeId ?: $this->getActiveThemeId();
        $headerPath = $this->themesDir . $themeId . '/header.php';
        if (file_exists($headerPath)) {
            return file_get_contents($headerPath);
        }
        return '';
    }

    public function saveHeaderContent(string $content, ?string $themeId = null): array {
        $themeId = $themeId ?: $this->getActiveThemeId();
        $headerPath = $this->themesDir . $themeId . '/header.php';
        $dir = dirname($headerPath);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        @chmod($dir, 0777);
        @chmod($headerPath, 0666);

        $res = @file_put_contents($headerPath, $content);
        if ($res === false) {
            @chmod($headerPath, 0777);
            @unlink($headerPath);
            $res = @file_put_contents($headerPath, $content);
        }

        if ($res !== false) {
            @chmod($headerPath, 0666);
            CMS::generateCache();
            CMS::gitCommit("Update theme header: $themeId");

            return ['status' => 'success', 'message' => 'Hlavička vzhledu byla úspěšně uložena.'];
        }

        return ['status' => 'error', 'message' => 'Chyba při zápisu do header.php.'];
    }

    public function getFooterContent(?string $themeId = null): string {
        $themeId = $themeId ?: $this->getActiveThemeId();
        $footerPath = $this->themesDir . $themeId . '/footer.php';
        if (file_exists($footerPath)) {
            return file_get_contents($footerPath);
        }
        return '';
    }

    public function saveFooterContent(string $content, ?string $themeId = null): array {
        $themeId = $themeId ?: $this->getActiveThemeId();
        $footerPath = $this->themesDir . $themeId . '/footer.php';
        $dir = dirname($footerPath);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        @chmod($dir, 0777);
        @chmod($footerPath, 0666);

        $res = @file_put_contents($footerPath, $content);
        if ($res === false) {
            @chmod($footerPath, 0777);
            @unlink($footerPath);
            $res = @file_put_contents($footerPath, $content);
        }

        if ($res !== false) {
            @chmod($footerPath, 0666);
            CMS::generateCache();
            CMS::gitCommit("Update theme footer: $themeId");

            return ['status' => 'success', 'message' => 'Patička vzhledu byla úspěšně uložena.'];
        }

        return ['status' => 'error', 'message' => 'Chyba při zápisu do footer.php.'];
    }

    public function renderHeader(): void {
        $themeId = $this->getActiveThemeId();
        $headerFile = $this->themesDir . $themeId . '/header.php';
        if (file_exists($headerFile)) {
            include_once $headerFile;
        } else {
            // Fallback header
            echo '<!DOCTYPE html><html lang="cs"><head><meta charset="UTF-8"><title>Fida CMS</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-950 text-white min-h-screen">';
        }
    }

    public function renderFooter(): void {
        $themeId = $this->getActiveThemeId();
        $footerFile = $this->themesDir . $themeId . '/footer.php';
        if (file_exists($footerFile)) {
            include_once $footerFile;
        } else {
            // Fallback footer
            echo '</body></html>';
        }
    }

    private function handleList(): array {
        $themes = $this->getInstalledThemes();
        return [
            'status' => 'success',
            'themes' => $themes,
            'active_theme' => $this->getActiveTheme()
        ];
    }

    private function handleActivate(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        $themeId = $data['theme_id'] ?? $_POST['theme_id'] ?? '';

        if (empty($themeId)) {
            return ['status' => 'error', 'message' => 'Nebyl vybrán žádný vzhled k aktivaci.'];
        }

        return $this->setActiveTheme($themeId);
    }

    private function handleSaveHeader(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        $content = $data['content'] ?? $_POST['content'] ?? '';

        return $this->saveHeaderContent($content);
    }

    private function handleSaveFooter(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        $content = $data['content'] ?? $_POST['content'] ?? '';

        return $this->saveFooterContent($content);
    }

    private function handleUpload(): array {
        if (!isset($_FILES['theme_file'])) {
            return ['status' => 'error', 'message' => 'Nebyl vybrán žádný ZIP soubor vzhledu.'];
        }

        $file = $_FILES['theme_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => 'error', 'message' => 'Chyba při nahrávání souboru.'];
        }

        $name = $file['name'];
        $tmpName = $file['tmp_name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($ext !== 'zip') {
            return ['status' => 'error', 'message' => 'Vzhled musí být ve formátu .zip archiv.'];
        }

        if (!class_exists('ZipArchive')) {
            return ['status' => 'error', 'message' => 'PHP ZipArchive rozšíření není dostupné na serveru.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpName) === TRUE) {
            $folderName = preg_replace("/[^a-z0-9_-]/i", "_", pathinfo($name, PATHINFO_FILENAME));
            $targetFolder = $this->themesDir . $folderName;

            if (!file_exists($targetFolder)) {
                @mkdir($targetFolder, 0777, true);
            }

            $zip->extractTo($targetFolder);
            $zip->close();

            CMS::gitCommit("Upload ZIP theme: $folderName");
            return ['status' => 'success', 'message' => "Vzhled $name byl nahrán a rozbalen."];
        }

        return ['status' => 'error', 'message' => 'Nepodařilo se otevřít ZIP archiv.'];
    }
}
