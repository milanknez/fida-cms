<?php

class FileManager {
    private string $rootDir;
    private string $uploadDir;
    private array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'avif', 
        'pdf', 'doc', 'docx', 'txt', 'rtf', 'xls', 'xlsx', 'csv', 
        'zip', 'rar', 'mp4', 'mp3'
    ];

    public function __construct(?string $rootDir = null, string $uploadSubDir = '/assets/img/uploads/') {
        if ($rootDir) {
            $this->rootDir = rtrim(realpath($rootDir) ?: $rootDir, '/');
        } else {
            $this->rootDir = rtrim(realpath(__DIR__ . '/../../') ?: realpath(__DIR__ . '/../') ?: __DIR__ . '/..', '/');
        }

        $this->uploadDir = $this->rootDir . '/' . ltrim($uploadSubDir, '/');
        if (!file_exists($this->uploadDir)) {
            @mkdir($this->uploadDir, 0777, true);
        }
        $this->uploadDir = rtrim(realpath($this->uploadDir) ?: $this->uploadDir, '/') . '/';
    }

    /**
     * Route and handle action requests.
     */
    public function handleRequest(string $action): array {
        switch ($action) {
            case 'list':
                return $this->handleList();
            case 'upload':
                return $this->handleUpload();
            case 'delete':
                return $this->handleDelete();
            default:
                return ['status' => 'error', 'message' => 'Neplatná akce.'];
        }
    }

    /**
     * List all asset files in upload directory.
     */
    public function getFilesList(): array {
        $filesList = [];
        $totalSizeBytes = 0;

        $assetsRootDir = $this->rootDir . '/assets';
        if (file_exists($assetsRootDir)) {
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($assetsRootDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $item) {
                    if ($item->isFile()) {
                        $filePath = $item->getPathname();
                        $ext = strtolower($item->getExtension());
                        if (in_array($ext, $this->allowedExtensions)) {
                            $size = $item->getSize();
                            $mtime = $item->getMTime();
                            $type = $this->detectFileType($ext);
                            $dimensions = $this->getImageDimensions($filePath, $type, $ext);
                            $relPath = ltrim(str_replace($this->rootDir, '', $filePath), '/');

                            $totalSizeBytes += $size;
                            $filesList[] = [
                                'name' => $item->getFilename(),
                                'url' => $relPath,
                                'size_formatted' => $this->formatBytes($size),
                                'raw_size' => $size,
                                'mtime_formatted' => date('d.m.Y H:i', $mtime),
                                'raw_mtime' => $mtime,
                                'ext' => $ext,
                                'type' => $type,
                                'dimensions' => $dimensions
                            ];
                        }
                    }
                }
            } catch (Exception $e) {}
        }

        // Sort newest first
        usort($filesList, fn($a, $b) => $b['raw_mtime'] <=> $a['raw_mtime']);

        return [
            'status' => 'success',
            'files' => $filesList,
            'total_size' => $totalSizeBytes,
            'total_size_formatted' => $this->formatBytes($totalSizeBytes)
        ];
    }

    private function handleList(): array {
        $data = $this->getFilesList();
        return [
            'status' => 'success',
            'files' => $data['files'],
            'stats' => [
                'count' => count($data['files']),
                'total_size_formatted' => $data['total_size_formatted']
            ]
        ];
    }

    private function handleUpload(): array {
        $filesArr = $_FILES['files'] ?? $_FILES['files[]'] ?? $_FILES['file'] ?? null;
        if (!$filesArr) {
            foreach ($_FILES as $key => $fileData) {
                $filesArr = $fileData;
                break;
            }
        }

        if (!$filesArr) {
            return ['status' => 'error', 'message' => 'Nebyly vybrány žádné soubory.'];
        }

        $uploadedFiles = [];
        $gjsData = [];
        $errors = [];

        $count = is_array($filesArr['name']) ? count($filesArr['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $tmpName = is_array($filesArr['tmp_name']) ? $filesArr['tmp_name'][$i] : $filesArr['tmp_name'];
            $name = is_array($filesArr['name']) ? $filesArr['name'][$i] : $filesArr['name'];
            $error = is_array($filesArr['error']) ? $filesArr['error'][$i] : $filesArr['error'];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "Chyba při nahrávání: $name";
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedExtensions)) {
                $errors[] = "Nepodporovaný typ souboru: $name";
                continue;
            }

            $rawBaseName = pathinfo($name, PATHINFO_FILENAME);
            $cleanBaseName = preg_replace("/[^a-z0-9_-]/i", "_", $rawBaseName);
            if (empty($cleanBaseName)) {
                $cleanBaseName = 'file_' . time();
            }

            $cleanName = $cleanBaseName . '.' . $ext;
            $target = $this->uploadDir . $cleanName;

            if (file_exists($target)) {
                $cleanName = $cleanBaseName . '_' . time() . '.' . $ext;
                $target = $this->uploadDir . $cleanName;
            }

            if (move_uploaded_file($tmpName, $target)) {
                @chmod($target, 0666);
                $relPath = 'assets/img/uploads/' . $cleanName;
                $fullUrl = '../' . $relPath;
                $uploadedFiles[] = $relPath;
                $gjsData[] = [
                    'src' => $fullUrl,
                    'name' => $cleanName,
                    'type' => $this->detectFileType($ext)
                ];
            } else {
                $errors[] = "Nepodařilo se uložit soubor: $name";
            }
        }

        if (!empty($uploadedFiles)) {
            CMS::gitCommit("Upload files: " . implode(', ', array_map('basename', $uploadedFiles)));
        }

        if (empty($uploadedFiles) && !empty($errors)) {
            return ['status' => 'error', 'message' => implode('; ', $errors)];
        }

        return [
            'status' => 'success',
            'message' => 'Soubory byly úspěšně nahrány.',
            'uploaded' => $uploadedFiles,
            'data' => $gjsData,
            'warnings' => $errors
        ];
    }

    private function handleDelete(): array {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        $filename = $data['filename'] ?? $_POST['filename'] ?? '';

        if (empty($filename)) {
            return ['status' => 'error', 'message' => 'Nebyl zadán název souboru k odstranění.'];
        }

        // Clean path and prevent path traversal
        $cleanPath = ltrim(str_replace(['\\', '..'], ['/', ''], $filename), '/');
        
        $targetPath = $this->rootDir . '/' . $cleanPath;
        if (!file_exists($targetPath)) {
            $targetPath = $this->uploadDir . basename($filename);
        }

        $realTarget = realpath($targetPath);
        $realAssetsDir = realpath($this->rootDir . '/assets');

        if (!$realTarget || !$realAssetsDir || strpos($realTarget, $realAssetsDir) !== 0) {
            return ['status' => 'error', 'message' => 'Soubor neexistuje nebo je mimo složku assets.'];
        }

        if (@unlink($realTarget)) {
            $relName = ltrim(str_replace($this->rootDir, '', $realTarget), '/');
            CMS::gitCommit("Delete asset file: $relName");
            return ['status' => 'success', 'message' => "Soubor " . basename($realTarget) . " byl smazán."];
        }

        return ['status' => 'error', 'message' => 'Nepodařilo se smazat soubor (chybí oprávnění).'];
    }

    private function detectFileType(string $ext): string {
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'avif'])) {
            return 'image';
        }
        if (in_array($ext, ['pdf', 'doc', 'docx', 'txt', 'rtf', 'xls', 'xlsx', 'csv', 'zip', 'rar'])) {
            return 'document';
        }
        return 'other';
    }

    private function getImageDimensions(string $filePath, string $type, string $ext): ?string {
        if ($type === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $imgSize = @getimagesize($filePath);
            if ($imgSize) {
                return $imgSize[0] . ' × ' . $imgSize[1];
            }
        }
        return null;
    }

    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
