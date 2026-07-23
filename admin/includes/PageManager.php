<?php

class PageManager {
    private string $rootDir;
    private string $pagesJsonFile;

    public function __construct(?string $rootDir = null) {
        if ($rootDir) {
            $this->rootDir = rtrim(realpath($rootDir) ?: $rootDir, '/');
        } else {
            $this->rootDir = rtrim(realpath(__DIR__ . '/../../') ?: realpath(__DIR__ . '/../') ?: __DIR__ . '/..', '/');
        }

        $this->pagesJsonFile = $this->rootDir . '/config/pages.json';
    }

    /**
     * Save HTML content and optional metadata for a page.
     */
    public function savePage(string $page, string $html, ?array $metaData = null): array {
        if (!preg_match('/\.(php|html)$/i', $page)) {
            $page .= '.php';
        }
        $targetPath = $this->rootDir . '/' . ltrim($page, '/');

        // Security check - prevent path traversal
        $realTarget = realpath($targetPath) ?: $targetPath;
        if (strpos($realTarget, $this->rootDir) !== 0) {
            return ['status' => 'error', 'message' => 'Neplatná cesta k souboru.'];
        }

        // Create backup & set write permissions
        @chmod(dirname($targetPath), 0777);
        @chmod($targetPath, 0666);
        if (file_exists($targetPath . '.bak')) {
            @chmod($targetPath . '.bak', 0666);
        }
        if (file_exists($targetPath)) {
            @copy($targetPath, $targetPath . '.bak');
        }

        // Save HTML with write permission fallback
        $res = @file_put_contents($targetPath, $html);
        if ($res === false) {
            @chmod($targetPath, 0777);
            @unlink($targetPath);
            $res = @file_put_contents($targetPath, $html);
        }

        if ($res !== false) {
            @chmod($targetPath, 0666);

            // Save Metadata
            if ($metaData && is_array($metaData)) {
                $this->updatePageMetadata($page, $metaData);
            }

            // Regenerate cache & git commit
            CMS::generateCache();
            $gitResult = CMS::gitCommit("Auto-update: $page");

            // Git se nepovede nebo je přeskočen → pouze lokální uložení
            $gitSkipped = (
                strpos($gitResult, 'ERROR:') === 0 ||
                strpos($gitResult, 'přeskočen') !== false ||
                strpos($gitResult, 'přesnut') !== false ||
                strpos($gitResult, 'vypnuta') !== false ||
                strpos($gitResult, 'Není nastaven') !== false
            );

            $status = 'success';
            $msg = $gitSkipped
                ? "Stránka $page byla uložena."
                : "Stránka $page byla uložena a commitnuta.";

            return [
                'status' => $status,
                'message' => $msg,
                'git_output' => $gitResult
            ];
        }

        return ['status' => 'error', 'message' => 'Chyba při zápisu do souboru.'];
    }

    /**
     * Delete page file and remove from config/pages.json.
     */
    public function deletePage(string $page): array {
        if (empty($page) || $page === 'index.php') {
            return ['status' => 'error', 'message' => 'Hlavní stránku (index.php) nelze smazat.'];
        }

        $targetPath = $this->rootDir . '/' . ltrim($page, '/');

        if (!file_exists($targetPath)) {
            return ['status' => 'error', 'message' => 'Soubor stránky neexistuje.'];
        }

        if (@unlink($targetPath)) {
            $pages = $this->getAllPagesMetadata();
            if (isset($pages[$page])) {
                unset($pages[$page]);
                $this->saveAllPagesMetadata($pages);
            }

            CMS::generateCache();
            CMS::gitCommit("Delete page: $page");

            return ['status' => 'success', 'message' => "Stránka $page byla odstraněna."];
        }

        return ['status' => 'error', 'message' => 'Chyba při mazání souboru stránky.'];
    }

    /**
     * Get metadata for all pages.
     */
    public function getAllPagesMetadata(): array {
        if (file_exists($this->pagesJsonFile)) {
            $json = @json_decode(@file_get_contents($this->pagesJsonFile), true);
            if (is_array($json)) {
                return $json;
            }
        }
        return [];
    }

    /**
     * Update metadata for a single page.
     */
    public function updatePageMetadata(string $page, array $metaData): bool {
        $pages = $this->getAllPagesMetadata();
        $pages[$page] = [
            'slug' => $metaData['slug'] ?? '',
            'title' => $metaData['title'] ?? '',
            'description' => $metaData['description'] ?? '',
            'keywords' => $metaData['keywords'] ?? ''
        ];

        return $this->saveAllPagesMetadata($pages);
    }

    /**
     * Save all pages metadata to config/pages.json.
     */
    private function saveAllPagesMetadata(array $pages): bool {
        $configDir = dirname($this->pagesJsonFile);
        if (!file_exists($configDir)) {
            @mkdir($configDir, 0777, true);
        }

        $json = json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $res = file_put_contents($this->pagesJsonFile, $json);
        if ($res !== false) {
            @chmod($this->pagesJsonFile, 0666);
            return true;
        }
        return false;
    }
}
