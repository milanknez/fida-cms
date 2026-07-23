<?php

class PluginManager {
    private string $rootDir;
    private string $pluginsDir;
    private string $configFile;

    public function __construct(?string $rootDir = null) {
        if ($rootDir) {
            $this->rootDir = rtrim(realpath($rootDir) ?: $rootDir, '/');
        } else {
            $this->rootDir = rtrim(realpath(__DIR__ . '/../../') ?: realpath(__DIR__ . '/../') ?: __DIR__ . '/..', '/');
        }

        $this->pluginsDir = $this->rootDir . '/plugins/';
        if (!file_exists($this->pluginsDir)) {
            @mkdir($this->pluginsDir, 0777, true);
        }

        $this->configFile = $this->rootDir . '/config/plugins.json';
    }

    /**
     * Dispatch HTTP action to corresponding handler.
     */
    public function handleRequest(string $action): array {
        switch ($action) {
            case 'list':
                return $this->handleList();
            case 'toggle':
                return $this->handleToggle();
            case 'upload':
                return $this->handleUpload();
            case 'delete':
                return $this->handleDelete();
            case 'get_smtp_config':
                return $this->handleGetSMTPConfig();
            case 'save_smtp_config':
                return $this->handleSaveSMTPConfig();
            case 'test_smtp':
                return $this->handleTestSMTP();
            default:
                return ['status' => 'error', 'message' => 'Neplatná akce.'];
        }
    }

    private function handleGetSMTPConfig(): array {
        $pluginFile = $this->pluginsDir . 'smtp-mailer/smtp-mailer.php';
        if (file_exists($pluginFile)) {
            require_once $pluginFile;
            if (class_exists('FidaSMTPMailer')) {
                return ['status' => 'success', 'config' => FidaSMTPMailer::getSMTPConfig()];
            }
        }
        return ['status' => 'error', 'message' => 'Plugin SMTP Mailer není k dispozici.'];
    }

    private function handleSaveSMTPConfig(): array {
        $pluginFile = $this->pluginsDir . 'smtp-mailer/smtp-mailer.php';
        if (!file_exists($pluginFile)) {
            return ['status' => 'error', 'message' => 'Plugin SMTP Mailer není k dispozici.'];
        }
        require_once $pluginFile;
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || !is_array($data)) {
            return ['status' => 'error', 'message' => 'Neplatná data.'];
        }

        if (class_exists('FidaSMTPMailer')) {
            FidaSMTPMailer::saveSMTPConfig($data);
            CMS::gitCommit("Update SMTP configuration");
            return ['status' => 'success', 'message' => 'Nastavení SMTP bylo úspěšně uloženo.'];
        }

        return ['status' => 'error', 'message' => 'Chyba při ukládání nastavení SMTP.'];
    }

    private function handleTestSMTP(): array {
        $pluginFile = $this->pluginsDir . 'smtp-mailer/smtp-mailer.php';
        if (!file_exists($pluginFile)) {
            return ['status' => 'error', 'message' => 'Plugin SMTP Mailer není nainstalován.'];
        }
        require_once $pluginFile;
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $testEmail = $data['test_email'] ?? '';

        if (empty($testEmail)) {
            $siteConfig = CMS::getSiteConfig();
            $testEmail = $siteConfig['email'] ?? 'test@example.com';
        }

        if (class_exists('FidaSMTPMailer')) {
            $subject = "Testovací e-mail SMTP | " . date('d.m.Y H:i:s');
            $body = "Dobrý den,\n\ntoto je testovací e-mail pro ověření funkčnosti vášho SMTP serveru ve Fida CMS.\n\nSpojení přes SMTP proběhlo úspěšně!\n\nDatum: " . date('d.m.Y H:i:s');
            $sent = FidaSMTPMailer::sendMail($testEmail, $subject, $body);

            if ($sent) {
                return ['status' => 'success', 'message' => "Testovací e-mail byl úspěšně odeslán na adresu $testEmail."];
            } else {
                return ['status' => 'error', 'message' => "Nepodařilo se odeslat testovací e-mail přes SMTP. Zkontrolujte přihlašovací údaje, port a šifrování."];
            }
        }

        return ['status' => 'error', 'message' => 'SMTP Mailer není dostupný.'];
    }

    /**
     * Get list of active plugin IDs from config.
     */
    public function getActivePlugins(): array {
        if (file_exists($this->configFile)) {
            $json = @json_decode(@file_get_contents($this->configFile), true);
            if (is_array($json) && isset($json['active_plugins']) && is_array($json['active_plugins'])) {
                return $json['active_plugins'];
            }
        }
        return [];
    }

    /**
     * Include all active plugin files into CMS context.
     */
    public function loadActivePlugins(): void {
        $active = $this->getActivePlugins();
        if (empty($active)) return;

        foreach ($active as $pluginFile) {
            $filePath = $this->pluginsDir . ltrim($pluginFile, '/');
            if (file_exists($filePath)) {
                include_once $filePath;
            }
        }
    }

    /**
     * Get list of all installed plugins with metadata.
     */
    public function getInstalledPlugins(): array {
        $activePlugins = $this->getActivePlugins();
        $installed = [];

        if (!file_exists($this->pluginsDir)) {
            return [];
        }

        $items = @scandir($this->pluginsDir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $itemPath = $this->pluginsDir . $item;

            if (is_dir($itemPath)) {
                $subFiles = @scandir($itemPath) ?: [];
                foreach ($subFiles as $subFile) {
                    if (pathinfo($subFile, PATHINFO_EXTENSION) === 'php') {
                        $subPath = $itemPath . '/' . $subFile;
                        $header = $this->parsePluginHeader($subPath);
                        if ($header) {
                            $pluginId = $item . '/' . $subFile;
                            $installed[] = [
                                'id' => $pluginId,
                                'name' => $header['name'],
                                'version' => $header['version'] ?: '1.0.0',
                                'description' => $header['description'] ?: 'Bez popisu.',
                                'author' => $header['author'] ?: 'Neznámý autor',
                                'active' => in_array($pluginId, $activePlugins)
                            ];
                        }
                    }
                }
            } elseif (is_file($itemPath) && pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $header = $this->parsePluginHeader($itemPath);
                if ($header) {
                    $pluginId = $item;
                    $installed[] = [
                        'id' => $pluginId,
                        'name' => $header['name'],
                        'version' => $header['version'] ?: '1.0.0',
                        'description' => $header['description'] ?: 'Bez popisu.',
                        'author' => $header['author'] ?: 'Neznámý autor',
                        'active' => in_array($pluginId, $activePlugins)
                    ];
                }
            }
        }

        return $installed;
    }

    private function handleList(): array {
        $plugins = $this->getInstalledPlugins();
        return [
            'status' => 'success',
            'plugins' => $plugins,
            'active_count' => count(array_filter($plugins, fn($p) => $p['active'])),
            'total_count' => count($plugins)
        ];
    }

    private function handleToggle(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $pluginId = $data['plugin_id'] ?? $_POST['plugin_id'] ?? '';
        $activeState = isset($data['active']) ? (bool)$data['active'] : (isset($_POST['active']) && $_POST['active'] === 'true');

        if (empty($pluginId)) {
            return ['status' => 'error', 'message' => 'Nebyl předán identifikátor pluginu.'];
        }

        $activePlugins = $this->getActivePlugins();

        if ($activeState) {
            if (!in_array($pluginId, $activePlugins)) {
                $activePlugins[] = $pluginId;
            }
            $msg = "Plugin byl aktivován.";
        } else {
            $activePlugins = array_values(array_diff($activePlugins, [$pluginId]));
            $msg = "Plugin byl deaktivován.";
        }

        if ($this->saveActivePlugins($activePlugins)) {
            CMS::gitCommit("Plugin status update: $pluginId (" . ($activeState ? 'activated' : 'deactivated') . ")");
            return [
                'status' => 'success',
                'message' => $msg,
                'plugin_id' => $pluginId,
                'active' => $activeState
            ];
        }

        return ['status' => 'error', 'message' => 'Nepodařilo se uložit stav pluginu (chyba zápisu do config/plugins.json).'];
    }

    private function handleUpload(): array {
        if (!isset($_FILES['plugin_file'])) {
            return ['status' => 'error', 'message' => 'Nebyl vybrán žádný soubor pluginu.'];
        }

        $file = $_FILES['plugin_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => 'error', 'message' => 'Chyba při nahrávání souboru.'];
        }

        $name = $file['name'];
        $tmpName = $file['tmp_name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($ext === 'zip') {
            if (!class_exists('ZipArchive')) {
                return ['status' => 'error', 'message' => 'PHP ZipArchive rozšíření není dostupné na serveru. Nahrávejte soubory .php.'];
            }

            $zip = new ZipArchive();
            if ($zip->open($tmpName) === TRUE) {
                $folderName = preg_replace("/[^a-z0-9_-]/i", "_", pathinfo($name, PATHINFO_FILENAME));
                $targetFolder = $this->pluginsDir . $folderName;

                if (!file_exists($targetFolder)) {
                    @mkdir($targetFolder, 0777, true);
                }

                $zip->extractTo($targetFolder);
                $zip->close();

                CMS::gitCommit("Upload ZIP plugin: $folderName");
                return ['status' => 'success', 'message' => "Plugin $name byl rozbalen a nainstalován."];
            }
            return ['status' => 'error', 'message' => 'Nepodařilo se otevřít ZIP archiv.'];
        }

        if ($ext === 'php') {
            $cleanName = preg_replace("/[^a-z0-9_-]/i", "_", pathinfo($name, PATHINFO_FILENAME)) . '.php';
            $targetFile = $this->pluginsDir . $cleanName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $header = $this->parsePluginHeader($targetFile);
                $msg = $header
                    ? "Plugin " . htmlspecialchars($header['name']) . " byl úspěšně nainstalován."
                    : "Plugin soubor byl nahrán. (Upozornění: Soubor neobsahuje standardní hlavičku 'Plugin Name: ...')";

                CMS::gitCommit("Upload PHP plugin: $cleanName");
                return ['status' => 'success', 'message' => $msg];
            }
            return ['status' => 'error', 'message' => 'Nepodařilo se uložit PHP soubor pluginu.'];
        }

        return ['status' => 'error', 'message' => 'Podporovány jsou pouze soubory .zip a .php.'];
    }

    private function handleDelete(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        $pluginId = $data['plugin_id'] ?? $_POST['plugin_id'] ?? '';

        if (empty($pluginId)) {
            return ['status' => 'error', 'message' => 'Nebyl zadán plugin k odstranění.'];
        }

        $safePluginId = ltrim(str_replace(['..', '\\'], '', $pluginId), '/');
        $fullPath = $this->pluginsDir . $safePluginId;

        if (!file_exists($fullPath)) {
            return ['status' => 'error', 'message' => 'Soubor nebo složka pluginu neexistuje.'];
        }

        $dirToDelete = is_dir($fullPath) ? $fullPath : (strpos($safePluginId, '/') !== false ? $this->pluginsDir . explode('/', $safePluginId)[0] : $fullPath);

        if ($this->recursiveDelete($dirToDelete)) {
            $activePlugins = array_values(array_diff($this->getActivePlugins(), [$pluginId]));
            $this->saveActivePlugins($activePlugins);

            CMS::gitCommit("Delete plugin: $safePluginId");
            return ['status' => 'success', 'message' => "Plugin $safePluginId byl odstraněn."];
        }

        return ['status' => 'error', 'message' => 'Chyba při mazání pluginu.'];
    }

    private function parsePluginHeader(string $filePath): ?array {
        $content = @file_get_contents($filePath, false, null, 0, 8192);
        if (!$content) return null;

        $headers = [
            'name' => 'Plugin Name',
            'version' => 'Version',
            'description' => 'Description',
            'author' => 'Author',
            'plugin_uri' => 'Plugin URI',
            'author_uri' => 'Author URI'
        ];

        $meta = [];
        foreach ($headers as $key => $headerLabel) {
            if (preg_match('/^[ \t\/*#]*' . preg_quote($headerLabel, '/') . ':\s*(.*)$/mi', $content, $matches)) {
                $meta[$key] = trim($matches[1]);
            } else {
                $meta[$key] = '';
            }
        }

        return !empty($meta['name']) ? $meta : null;
    }

    private function saveActivePlugins(array $activeList): bool {
        $data = ['active_plugins' => array_values(array_unique($activeList))];
        $configDir = dirname($this->configFile);

        if (!file_exists($configDir)) {
            @mkdir($configDir, 0777, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $res = file_put_contents($this->configFile, $json);
        if ($res !== false) {
            @chmod($this->configFile, 0666);
            return true;
        }

        @chmod($this->configFile, 0666);
        @chmod($configDir, 0777);
        $res = file_put_contents($this->configFile, $json);
        if ($res !== false) {
            return true;
        }

        @unlink($this->configFile);
        $res = @file_put_contents($this->configFile, $json);
        if ($res !== false) {
            @chmod($this->configFile, 0666);
            return true;
        }

        return false;
    }

    private function recursiveDelete(string $dir): bool {
        if (is_file($dir)) return @unlink($dir);
        if (!is_dir($dir)) return false;
        $files = @array_diff(@scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->recursiveDelete("$dir/$file") : @unlink("$dir/$file");
        }
        return @rmdir($dir);
    }
}
