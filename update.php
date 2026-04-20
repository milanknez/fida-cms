<?php
require_once 'config.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$action = $_GET['action'] ?? 'check';
$response = ['status' => 'success', 'message' => ''];

// GitHub Raw URL for config.php to check version
$githubConfigUrl = 'https://raw.githubusercontent.com/milanknez/fida-cms/main/admin/config.php';
$githubZipUrl = 'https://github.com/milanknez/fida-cms/archive/refs/heads/main.zip';

if ($action === 'check') {
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
            // ZIP contains a subfolder fida-cms-main, we need to extract its content
            $tempFolder = 'update_extract_temp/';
            $zip->extractTo($tempFolder);
            $zip->close();
            
            // Move files from fida-cms-main/admin/* to current folder
            $sourceAdmin = $tempFolder . 'fida-cms-main/admin/';
            if (is_dir($sourceAdmin)) {
                $files = scandir($sourceAdmin);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === 'config.php') continue; // Don't overwrite local config!
                    copy($sourceAdmin . $file, './' . $file);
                }
                
                // Cleanup
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
                rrmdir($tempFolder);
                unlink($zipFile);
                
                $response['message'] = 'Aktualizace proběhla úspěšně! Vaše nastavení v config.php zůstalo zachováno.';
            } else {
                $response = ['status' => 'error', 'message' => 'V archivu nebyla nalezena složka admin/.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Nepodařilo se otevřít ZIP archiv (chybí rozšíření ZipArchive?).'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
