<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$action = $_GET['action'] ?? 'check';
$response = ['status' => 'success', 'message' => ''];

// GitHub URLs (with cache buster for version check)
$githubVersionUrl = 'https://raw.githubusercontent.com/milanknez/fida-cms/main/version.php?t=' . time();
$githubZipUrl = 'https://github.com/milanknez/fida-cms/archive/refs/heads/main.zip';

if ($action === 'check') {
    $remoteVersionContent = @file_get_contents($githubVersionUrl);
    if ($remoteVersionContent && preg_match("/define\('APP_VERSION', '(.*?)'\)/", $remoteVersionContent, $matches)) {
        $remoteVersion = $matches[1];
        if (version_compare($remoteVersion, APP_VERSION, '>')) {
            $response['updates_available'] = true;
            $response['message'] = "Nová verze $remoteVersion je k dispozici!";
        } else {
            $response['updates_available'] = false;
            $response['message'] = 'Máte aktuální verzi (v' . APP_VERSION . ').';
        }
    } else {
        // Fallback search in config.php if version.php is not found yet
        $githubConfigUrl = 'https://raw.githubusercontent.com/milanknez/fida-cms/main/config.php?t=' . time();
        $remoteConfig = @file_get_contents($githubConfigUrl);
        if ($remoteConfig && preg_match("/define\('APP_VERSION', '(.*?)'\)/", $remoteConfig, $matches)) {
            $remoteVersion = $matches[1];
            if (version_compare($remoteVersion, APP_VERSION, '>')) {
                $response['updates_available'] = true;
                $response['message'] = "Nová verze $remoteVersion je k dispozici!";
            } else {
                $response['updates_available'] = false;
                $response['message'] = 'Máte aktuální verzi.';
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Nepodařilo se ověřit verzi na GitHubu.'];
        }
    }
} elseif ($action === 'pull') {
    // Download ZIP
    $zipFile = 'update_temp.zip';
    $content = @file_get_contents($githubZipUrl);
    
    if (!$content) {
        $response = ['status' => 'error', 'message' => 'Nepodařilo se stáhnout aktualizační balíček.'];
    } else {
        file_put_contents($zipFile, $content);
        
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $tempFolder = 'update_extract_temp/';
            $zip->extractTo($tempFolder);
            $zip->close();
            
            // Move files from fida-cms-main/* to current folder (admin/)
            $sourceRoot = $tempFolder . 'fida-cms-main/';
            if (is_dir($sourceRoot)) {
                $files = scandir($sourceRoot);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === 'config.php' || $file === '.git') continue; 
                    
                    $src = $sourceRoot . $file;
                    $dst = './' . $file;

                    if (is_dir($src)) {
                        if (!is_dir($dst)) mkdir($dst);
                        $subFiles = scandir($src);
                        foreach($subFiles as $sf) {
                            if ($sf !== '.' && $sf !== '..') copy($src . '/' . $sf, $dst . '/' . $sf);
                        }
                    } else {
                        copy($src, $dst);
                    }
                }
                
                // Cleanup function
                $rrmdir = function ($dir) use (&$rrmdir) {
                    if (is_dir($dir)) {
                        $objects = scandir($dir);
                        foreach ($objects as $object) {
                            if ($object != "." && $object != "..") {
                                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                                    $rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                                else
                                    unlink($dir. DIRECTORY_SEPARATOR .$object);
                            }
                        }
                        rmdir($dir);
                    }
                };
                
                $rrmdir($tempFolder);
                unlink($zipFile);
                
                $response['message'] = 'Aktualizace proběhla úspěšně!';
            } else {
                $response = ['status' => 'error', 'message' => 'V archivu nebyla nalezena data.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Nepodařilo se otevřít ZIP archiv (ZipArchive?).'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
