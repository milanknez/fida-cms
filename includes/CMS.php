<?php

class CMS {
    private static $siteConfig = null;
    private static $pagesConfig = null;

    public static function getSiteConfig() {
        if (self::$siteConfig === null) {
            $path = __DIR__ . '/../../config/site.json';
            if (!file_exists($path)) {
                $path = __DIR__ . '/../config/site.json';
            }
            if (file_exists($path)) {
                self::$siteConfig = json_decode(file_get_contents($path), true);
            } else {
                self::$siteConfig = [];
            }
        }
        return self::$siteConfig;
    }

    public static function getPagesConfig() {
        if (self::$pagesConfig === null) {
            $path = __DIR__ . '/../../config/pages.json';
            if (!file_exists($path)) {
                $path = __DIR__ . '/../config/pages.json';
            }
            if (file_exists($path)) {
                self::$pagesConfig = json_decode(file_get_contents($path), true);
            } else {
                self::$pagesConfig = [];
            }
        }
        return self::$pagesConfig;
    }

    public static function loadActivePlugins() {
        require_once __DIR__ . '/PluginManager.php';
        $manager = new PluginManager();
        $manager->loadActivePlugins();
    }

    public static function sendMail($to, $subject, $body, $headers = '') {
        self::loadActivePlugins();
        if (class_exists('FidaSMTPMailer')) {
            return FidaSMTPMailer::sendMail($to, $subject, $body, $headers);
        }
        return @mail($to, $subject, $body, $headers);
    }

    public static function getHeader() {
        require_once __DIR__ . '/ThemeManager.php';
        $manager = new ThemeManager();
        $manager->renderHeader();
    }

    public static function getFooter() {
        require_once __DIR__ . '/ThemeManager.php';
        $manager = new ThemeManager();
        $manager->renderFooter();
    }

    public static function getPageMeta($pageName = null) {
        self::loadActivePlugins();
        $pages = self::getPagesConfig();
        $siteConfig = self::getSiteConfig();
        $siteName = $siteConfig['site_name'] ?? 'Moje Webové Stránky';

        if ($pageName === null) {
            if (defined('CURRENT_PAGE')) {
                $pageName = CURRENT_PAGE;
            } else {
                $scriptName = basename($_SERVER['SCRIPT_NAME']);
                
                if (isset($pages[$scriptName])) {
                    $pageName = $scriptName;
                } else {
                    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                    $path = parse_url($requestUri, PHP_URL_PATH);
                    $slug = trim($path, '/');
                    
                    if (empty($slug) || $slug === 'index' || $slug === 'index.php') {
                        $pageName = 'index.php';
                    } else {
                        foreach ($pages as $file => $config) {
                            if (isset($config['slug']) && $config['slug'] === $slug) {
                                $pageName = $file;
                                break;
                            }
                        }
                        
                        if ($pageName === null) {
                            $potentialFile = $slug . '.php';
                            if (file_exists($potentialFile)) {
                                $pageName = $potentialFile;
                            } else {
                                $pageName = 'index.php';
                            }
                        }
                    }
                }
            }
        }
        
        $meta = $pages[$pageName] ?? [
            'slug' => str_replace('.php', '', $pageName),
            'title' => $siteName,
            'description' => '',
            'keywords' => ''
        ];

        if (empty($meta['title'])) {
            $meta['title'] = $siteName;
        }

        return $meta;
    }

    public static function getBasePath() {
        $base = str_replace(['/includes/CMS.php', '/admin/includes/CMS.php', '/admin/save.php', '/admin/index.php', '/router.php', '/index.php'], '', $_SERVER['SCRIPT_NAME']);
        return rtrim($base, '/');
    }

    public static function url($file) {
        $base = self::getBasePath();
        
        if ($file === 'index.php') return $base . '/';
        
        $pages = self::getPagesConfig();
        if (isset($pages[$file]) && !empty($pages[$file]['slug'])) {
            return $base . '/' . $pages[$file]['slug'];
        }
        return $base . '/' . str_replace('.php', '', $file);
    }

    public static function isUpdateAvailable() {
        $rootDir = realpath(__DIR__ . '/../');
        if (!file_exists($rootDir . '/config.php')) return false;
        require_once $rootDir . '/config.php';
        
        if (!defined('REPO_URL') || !defined('APP_VERSION')) return false;
        
        $repoClean = str_replace('.git', '', REPO_URL);
        $repoParts = explode('github.com/', $repoClean);
        if (count($repoParts) < 2) return false;
        
        $repoPath = $repoParts[1];
        $branch = 'main';
        $githubVersionUrl = "https://raw.githubusercontent.com/$repoPath/$branch/admin/version.php?nocache=" . uniqid();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $githubVersionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FidaCMS-UpdateChecker-' . uniqid());
        $remoteVersionFile = curl_exec($ch);
        curl_close($ch);
        
        if ($remoteVersionFile && preg_match("/define\('APP_VERSION', '(.*?)'\)/", $remoteVersionFile, $matches)) {
            $remoteVersion = $matches[1];
            return version_compare($remoteVersion, APP_VERSION, '>');
        }
        return false;
    }

    public static function gitCommit($message) {
        $rootDir = realpath(__DIR__ . '/../../');
        if (!file_exists($rootDir . '/admin/config.php')) {
            $rootDir = realpath(__DIR__ . '/../');
        }
        require_once $rootDir . '/admin/config.php';
        
        if (defined('ENABLE_PROJECT_GIT') && ENABLE_PROJECT_GIT === false) {
            return "Git commit přeskočen (synchronizace projektu je v nastavení vypnuta).";
        }

        if (!defined('REPO_URL') || !defined('GITHUB_TOKEN') || empty(GITHUB_TOKEN)) {
            return "Git commit přesunut/přeskočen: Není nastaven GITHUB_TOKEN pro tento projekt.";
        }

        $repoClean = str_replace('.git', '', REPO_URL);
        $repoParts = explode('github.com/', $repoClean);
        if (count($repoParts) < 2) return "ERROR: Neplatné REPO_URL.";
        
        $repoPath = $repoParts[1];
        $branch = 'main';
        $token = GITHUB_TOKEN;

        $filesToPush = [];

        if (isset($_SESSION['current_page'])) {
            $page = $_SESSION['current_page'];
            $pagePath = $rootDir . '/' . $page;
            if (file_exists($pagePath)) {
                $filesToPush[$page] = file_get_contents($pagePath);
            }
        }
        
        $pagesJson = $rootDir . '/config/pages.json';
        if (file_exists($pagesJson)) {
            $filesToPush['config/pages.json'] = file_get_contents($pagesJson);
        }

        $siteJson = $rootDir . '/config/site.json';
        if (file_exists($siteJson)) {
            $filesToPush['config/site.json'] = file_get_contents($siteJson);
        }

        if (empty($filesToPush)) return "Žádné změny k nahrání.";

        $uploadDir = $rootDir . '/assets/img/uploads/';
        if (file_exists($uploadDir)) {
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filesToPush['assets/img/uploads/' . $file] = file_get_contents($uploadDir . $file);
                }
            }
        }

        $cacheDir = $rootDir . '/cache/';
        if (file_exists($cacheDir)) {
            $files = scandir($cacheDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filesToPush['cache/' . $file] = file_get_contents($cacheDir . $file);
                }
            }
        }

        return self::pushMultipleToGitHubAPI($repoPath, $filesToPush, $message, $branch, $token);
    }

    private static function pushMultipleToGitHubAPI($repo, $files, $message, $branch, $token) {
        $headers = [
            'Authorization: token ' . $token,
            'User-Agent: FidaCMS-Editor',
            'Accept: application/vnd.github.v3+json',
            'Content-Type: application/json'
        ];

        $res = self::githubRequest("GET", "https://api.github.com/repos/$repo/git/refs/heads/$branch", null, $headers);
        $lastCommitSha = $res['object']['sha'] ?? null;
        if (!$lastCommitSha) return "ERROR: Nepodařilo se získat SHA poslední revize.";

        $treeData = ['base_tree' => $lastCommitSha, 'tree' => []];
        $binaryExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'pdf'];

        foreach ($files as $path => $content) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $treeEntry = [
                'path' => $path,
                'mode' => '100644',
                'type' => 'blob'
            ];

            if (in_array($ext, $binaryExtensions)) {
                $blobRes = self::githubRequest("POST", "https://api.github.com/repos/$repo/git/blobs", [
                    'content' => base64_encode($content),
                    'encoding' => 'base64'
                ], $headers);
                
                if (isset($blobRes['sha'])) {
                    $treeEntry['sha'] = $blobRes['sha'];
                } else {
                    return "ERROR: Selhalo nahrávání binárního souboru $path";
                }
            } else {
                $treeEntry['content'] = $content;
            }

            $treeData['tree'][] = $treeEntry;
        }

        $newTreeRes = self::githubRequest("POST", "https://api.github.com/repos/$repo/git/trees", $treeData, $headers);
        $newTreeSha = $newTreeRes['sha'] ?? null;
        if (!$newTreeSha) return "ERROR: Nepodařilo se vytvořit nový Git strom.";

        $commitRes = self::githubRequest("POST", "https://api.github.com/repos/$repo/git/commits", [
            'message' => $message,
            'tree' => $newTreeSha,
            'parents' => [$lastCommitSha]
        ], $headers);
        $newCommitSha = $commitRes['sha'] ?? null;
        if (!$newCommitSha) return "ERROR: Nepodařilo se vytvořit commit.";

        $refRes = self::githubRequest("PATCH", "https://api.github.com/repos/$repo/git/refs/heads/$branch", [
            'sha' => $newCommitSha,
            'force' => false
        ], $headers);

        if (isset($refRes['object']['sha'])) {
            return "SUCCESS: Změny byly úspěšně nahrány na GitHub ($newCommitSha).";
        }

        return "ERROR: Nepodařilo se aktualizovat větev $branch.";
    }

    private static function githubRequest($method, $url, $data = null, $headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public static function generateCache() {
        $siteConfig = self::getSiteConfig();
        $cacheEnabled = $siteConfig['enable_cache'] ?? false;
        
        $rootDir = realpath(__DIR__ . '/../../');
        if (!file_exists($rootDir . '/admin/config.php')) {
            $rootDir = realpath(__DIR__ . '/../');
        }
        $cacheDir = $rootDir . '/cache/';
        
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (!$cacheEnabled) {
            $files = glob($cacheDir . '*.html');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            return false;
        }

        $pages = self::getPagesConfig();
        $created = [];

        foreach ($pages as $file => $config) {
            $filePath = $rootDir . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                
                $slug = $config['slug'] ?? str_replace('.php', '', $file);
                if ($slug === 'index' || $file === 'index.php') {
                    $cacheFile = $cacheDir . 'index.html';
                } else {
                    $cacheFile = $cacheDir . $slug . '.html';
                }

                file_put_contents($cacheFile, $content);
                $created[] = $cacheFile;
            }
        }
        return $created;
    }
}
