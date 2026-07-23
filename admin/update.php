<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'check';
$response = ['status' => 'success', 'message' => ''];

if ($action === 'save_repo') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $repoUrl = trim($data['repo_url'] ?? '');
    $githubToken = trim($data['github_token'] ?? '');
    
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        $content = file_get_contents($configPath);
        $content = preg_replace("/define\('REPO_URL',\s*'.*?'\);/", "define('REPO_URL', '" . addslashes($repoUrl) . "');", $content);
        $content = preg_replace("/define\('GITHUB_TOKEN',\s*'.*?'\);/", "define('GITHUB_TOKEN', '" . addslashes($githubToken) . "');", $content);
        file_put_contents($configPath, $content);
        echo json_encode(['status' => 'success', 'message' => 'Nastavení repozitáře bylo uloženo.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Soubor config.php nebyl nalezen.']);
    }
    exit;
}

$repoUrl = REPO_URL;
$repoClean = str_replace('.git', '', $repoUrl);
$repoParts = explode('github.com/', $repoClean);

if (count($repoParts) < 2) {
    $response = ['status' => 'error', 'message' => 'Invalid REPO_URL in config.php. Must be a GitHub URL.'];
    echo json_encode($response);
    exit;
}

$repoPath = $repoParts[1];
$branch = 'main';

$githubVersionUrl = "https://raw.githubusercontent.com/$repoPath/$branch/admin/version.php?nocache=" . uniqid();
$githubZipUrl = "https://github.com/$repoPath/archive/refs/heads/$branch.zip";

if ($action === 'check') {
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
        $isAvailable = version_compare($remoteVersion, APP_VERSION, '>');
        if ($isAvailable) {
            $response['updates_available'] = true;
            $response['message'] = "Nová verze $remoteVersion je k dispozici!";
            $response['version'] = $remoteVersion;
            $response['local_version'] = APP_VERSION;
            $response['remote_version'] = $remoteVersion;
        } else {
            $response['updates_available'] = false;
            $response['message'] = 'Máte aktuální verzi.';
            $response['local_version'] = APP_VERSION;
            $response['remote_version'] = $remoteVersion;
        }
    } else {
        $response = [
            'status' => 'error', 
            'message' => 'Nepodařilo se ověřit verzi na GitHubu.',
            'local_version' => APP_VERSION
        ];
    }
} elseif ($action === 'pull') {
    $zipFile = 'update_temp.zip';
    $token = defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '';
    
    $downloadUrl = !empty($token) 
        ? "https://api.github.com/repos/$repoPath/zipball/$branch" 
        : $githubZipUrl;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_USERAGENT, 'FidaCMS-Updater');
    
    $headers = ['Accept: application/vnd.github.v3+json'];
    if (!empty($token)) {
        $headers[] = 'Authorization: token ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (!$content || $httpCode !== 200) {
        $response = ['status' => 'error', 'message' => 'Nepodařilo se stáhnout aktualizační balíček z ' . $githubZipUrl . ' (HTTP ' . $httpCode . ')'];
    } else {
        file_put_contents($zipFile, $content);
        
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $tempFolder = 'update_extract_temp/';
                if (!is_dir($tempFolder)) mkdir($tempFolder, 0755, true);
                $zip->extractTo($tempFolder);
                $zip->close();
                
                $extractedDirs = array_filter(glob($tempFolder . '*'), 'is_dir');
                $sourceDir = reset($extractedDirs);
                
                if ($sourceDir && is_dir($sourceDir)) {
                    if (!function_exists('copyRecursive')) {
                        function copyRecursive($src, $dst, $exclude = []) {
                            $dir = opendir($src);
                            @mkdir($dst);
                            while(false !== ( $file = readdir($dir)) ) {
                                if (( $file != '.' ) && ( $file != '..' )) {
                                    $srcFile = $src . '/' . $file;
                                    $dstFile = $dst . '/' . $file;
                                    
                                    $isExcluded = false;
                                    foreach ($exclude as $ex) {
                                        if (strpos($dstFile, $ex) !== false) {
                                            $isExcluded = true;
                                            break;
                                        }
                                    }
                                    if ($isExcluded) continue;

                                    if ( is_dir($srcFile) ) {
                                        copyRecursive($srcFile, $dstFile, $exclude);
                                    } else {
                                        copy($srcFile, $dstFile);
                                    }
                                }
                            }
                            closedir($dir);
                        }
                    }

                    $exclude = [
                        'admin/config.php',
                        '.git'
                    ];
                    
                    $rootDir = realpath(__DIR__ . '/../');
                    copyRecursive($sourceDir, $rootDir, $exclude);
                    
                    if (!function_exists('rrmdir')) {
                        function rrmdir($dir) {
                            if (is_dir($dir)) {
                                $objects = scandir($dir);
                                foreach ($objects as $object) {
                                    if ($object != "." && $object != "..") {
                                        if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                                            rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                                        else
                                            unlink($dir. DIRECTORY_SEPARATOR .$object);
                                    }
                                }
                                rmdir($dir);
                            }
                        }
                    }
                    rrmdir($tempFolder);
                    unlink($zipFile);

                    $response['message'] = 'Aktualizace proběhla úspěšně! Všechny soubory byly aktualizovány.';
                } else {
                    $response = ['status' => 'error', 'message' => 'V archivu nebyla nalezena zdrojová složka.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Nepodařilo se otevřít stažený ZIP archiv.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Na serveru chybí PHP rozšíření ZipArchive.'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
