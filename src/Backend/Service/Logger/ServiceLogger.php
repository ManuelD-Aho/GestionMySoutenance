<?php

namespace App\Backend\Service\Logger;

use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use PDO;

class ServiceLogger implements ServiceLoggerInterface
{
    private PDO $db;
    private ServiceSupervisionInterface $supervisionService;
    private string $logPath;

    public function __construct(PDO $db, ServiceSupervisionInterface $supervisionService)
    {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->logPath = ROOT_PATH . '/logs/';

        // Créer le dossier de logs s'il n'existe pas
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function getLogFiles(): array
    {
        $files = [];
        $directory = new \DirectoryIterator($this->logPath);

        foreach ($directory as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isFile() || $fileInfo->getExtension() !== 'log') {
                continue;
            }

            $files[] = [
                'name' => $fileInfo->getFilename(),
                'size' => $fileInfo->getSize(),
                'size_formatted' => $this->formatFileSize($fileInfo->getSize()),
                'modified' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                'lines' => $this->countFileLines($fileInfo->getPathname())
            ];
        }

        // Trier par date de modification (plus récent en premier)
        usort($files, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        return $files;
    }

    public function getLogStats(): array
    {
        $files = $this->getLogFiles();
        $totalSize = array_sum(array_column($files, 'size'));
        $totalLines = array_sum(array_column($files, 'lines'));

        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatFileSize($totalSize),
            'total_lines' => $totalLines,
            'oldest_file' => !empty($files) ? end($files)['modified'] : null,
            'newest_file' => !empty($files) ? $files[0]['modified'] : null
        ];
    }

    public function getRecentErrors(int $limit): array
    {
        $errors = [];
        $files = $this->getLogFiles();

        // Chercher dans les fichiers les plus récents
        foreach (array_slice($files, 0, 3) as $file) {
            $filePath = $this->logPath . $file['name'];
            $content = $this->readLogFileReverse($filePath, 200); // Lire les 200 dernières lignes

            foreach ($content as $line) {
                if (preg_match('/\[(ERROR|CRITICAL|EMERGENCY)\]/', $line)) {
                    $errors[] = [
                        'file' => $file['name'],
                        'line' => $line,
                        'timestamp' => $this->extractTimestampFromLine($line)
                    ];

                    if (count($errors) >= $limit) {
                        break 2;
                    }
                }
            }
        }

        return $errors;
    }

    public function getLogContent(string $file, array $filters = [], int $page = 1, int $limit = 100): array
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Fichier de log non trouvé: $file");
        }

        $allLines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filteredLines = $this->applyLogFilters($allLines, $filters);

        $total = count($filteredLines);
        $offset = ($page - 1) * $limit;
        $pageLines = array_slice($filteredLines, $offset, $limit);

        return [
            'entries' => $this->parseLogLines($pageLines),
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }

    private function applyLogFilters(array $lines, array $filters): array
    {
        if (empty($filters)) {
            return $lines;
        }

        return array_filter($lines, function($line) use ($filters) {
            // Filtre par niveau
            if (!empty($filters['level'])) {
                if (!preg_match('/\[' . preg_quote($filters['level']) . '\]/', $line)) {
                    return false;
                }
            }

            // Filtre par recherche textuelle
            if (!empty($filters['search'])) {
                if (stripos($line, $filters['search']) === false) {
                    return false;
                }
            }

            // Filtre par date
            if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
                $timestamp = $this->extractTimestampFromLine($line);
                if ($timestamp) {
                    if (!empty($filters['date_debut']) && $timestamp < $filters['date_debut']) {
                        return false;
                    }
                    if (!empty($filters['date_fin']) && $timestamp > $filters['date_fin']) {
                        return false;
                    }
                }
            }

            return true;
        });
    }

    private function parseLogLines(array $lines): array
    {
        $entries = [];

        foreach ($lines as $line) {
            $entry = [
                'raw' => $line,
                'timestamp' => $this->extractTimestampFromLine($line),
                'level' => $this->extractLevelFromLine($line),
                'message' => $this->extractMessageFromLine($line),
                'context' => $this->extractContextFromLine($line)
            ];

            $entries[] = $entry;
        }

        return $entries;
    }

    public function getLogFileInfo(string $file): array
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Fichier de log non trouvé: $file");
        }

        $stat = stat($filePath);

        return [
            'name' => $file,
            'size' => $stat['size'],
            'size_formatted' => $this->formatFileSize($stat['size']),
            'created' => date('Y-m-d H:i:s', $stat['ctime']),
            'modified' => date('Y-m-d H:i:s', $stat['mtime']),
            'lines' => $this->countFileLines($filePath),
            'readable' => is_readable($filePath),
            'writable' => is_writable($filePath)
        ];
    }

    public function clearLogFile(string $file): bool
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Fichier de log non trouvé: $file");
        }

        return file_put_contents($filePath, '') !== false;
    }

    public function downloadLogFile(string $file): void
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Fichier de log non trouvé: $file");
        }

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function getLogFileSize(string $file): string
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            return '0 B';
        }

        return $this->formatFileSize(filesize($filePath));
    }

    public function archiveOldLogs(int $retentionDays): array
    {
        $archived = 0;
        $cutoffDate = time() - ($retentionDays * 24 * 3600);
        $archivePath = $this->logPath . 'archive/';

        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }

        $files = $this->getLogFiles();

        foreach ($files as $file) {
            $filePath = $this->logPath . $file['name'];
            $modTime = filemtime($filePath);

            if ($modTime < $cutoffDate) {
                $archiveFile = $archivePath . date('Y-m-d_', $modTime) . $file['name'] . '.gz';

                // Compresser et déplacer
                if ($this->compressFile($filePath, $archiveFile)) {
                    unlink($filePath);
                    $archived++;
                }
            }
        }

        return ['archived' => $archived];
    }

    public function analyzeLogFile(string $file): array
    {
        $filePath = $this->logPath . $file;
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Fichier de log non trouvé: $file");
        }

        $analysis = [
            'file_size' => filesize($filePath),
            'total_lines' => $this->countFileLines($filePath),
            'level_distribution' => [],
            'error_rate' => 0,
            'top_errors' => [],
            'memory_warnings' => 0,
            'time_range' => ['start' => null, 'end' => null]
        ];

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $errorCount = 0;
        $errorMessages = [];

        foreach ($lines as $line) {
            $level = $this->extractLevelFromLine($line);
            if ($level) {
                $analysis['level_distribution'][$level] = ($analysis['level_distribution'][$level] ?? 0) + 1;

                if (in_array($level, ['ERROR', 'CRITICAL', 'EMERGENCY'])) {
                    $errorCount++;
                    $message = $this->extractMessageFromLine($line);
                    $errorMessages[] = $message;
                }

                if (stripos($line, 'memory') !== false) {
                    $analysis['memory_warnings']++;
                }
            }

            $timestamp = $this->extractTimestampFromLine($line);
            if ($timestamp) {
                if (!$analysis['time_range']['start'] || $timestamp < $analysis['time_range']['start']) {
                    $analysis['time_range']['start'] = $timestamp;
                }
                if (!$analysis['time_range']['end'] || $timestamp > $analysis['time_range']['end']) {
                    $analysis['time_range']['end'] = $timestamp;
                }
            }
        }

        $analysis['error_rate'] = $analysis['total_lines'] > 0 ?
            round(($errorCount / $analysis['total_lines']) * 100, 2) : 0;

        $analysis['top_errors'] = array_slice(array_count_values($errorMessages), 0, 10, true);

        return $analysis;
    }

    public function getLogConfiguration(): array
    {
        // Configuration par défaut ou depuis la base
        return [
            'global_level' => 'INFO',
            'module_levels' => [
                'authentication' => 'WARNING',
                'database' => 'ERROR',
                'security' => 'DEBUG'
            ],
            'rotate_size' => 10, // MB
            'retention_days' => 30
        ];
    }

    public function updateLogConfiguration(array $config): bool
    {
        // Sauvegarder la configuration en base ou fichier
        return true;
    }

    public function getLogModules(): array
    {
        return [
            'authentication' => 'Authentification',
            'database' => 'Base de données',
            'security' => 'Sécurité',
            'reports' => 'Rapports',
            'commission' => 'Commission',
            'email' => 'Email',
            'file_upload' => 'Upload de fichiers'
        ];
    }

    public function exportLogs(array $criteria): void
    {
        $filename = 'logs_export_' . date('Y-m-d_H-i-s');
        $format = $criteria['format'] ?? 'txt';

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $format . '"');

        foreach ($criteria['files'] as $file) {
            echo "=== $file ===\n";
            $content = $this->getLogContent($file, $criteria);
            foreach ($content['entries'] as $entry) {
                echo $entry['raw'] . "\n";
            }
            echo "\n";
        }

        exit;
    }

    // Méthodes utilitaires privées
    private function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return round($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    private function countFileLines(string $filePath): int
    {
        $count = 0;
        $handle = fopen($filePath, 'r');
        if ($handle) {
            while (!feof($handle)) {
                $line = fgets($handle);
                if ($line !== false) {
                    $count++;
                }
            }
            fclose($handle);
        }
        return $count;
    }

    private function readLogFileReverse(string $filePath, int $lines): array
    {
        // Lecture inverse d'un fichier (pour récupérer les dernières lignes)
        $result = [];
        $file = new \SplFileObject($filePath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $startLine = max(0, $totalLines - $lines);

        for ($i = $totalLines; $i >= $startLine; $i--) {
            $file->seek($i);
            $line = $file->current();
            if (trim($line) !== '') {
                $result[] = trim($line);
            }
        }

        return $result;
    }

    private function extractTimestampFromLine(string $line): ?string
    {
        // Format: [2025-01-07 14:30:45]
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractLevelFromLine(string $line): ?string
    {
        if (preg_match('/\[(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\]/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractMessageFromLine(string $line): string
    {
        // Supprimer timestamp et niveau pour ne garder que le message
        $cleaned = preg_replace('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', '', $line);
        $cleaned = preg_replace('/\[(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\]/', '', $cleaned);
        return trim($cleaned);
    }

    private function extractContextFromLine(string $line): array
    {
        // Extraire le contexte JSON s'il existe
        if (preg_match('/\{.*\}$/', $line, $matches)) {
            $json = json_decode($matches[0], true);
            return $json ?: [];
        }
        return [];
    }

    private function compressFile(string $source, string $destination): bool
    {
        $bufferSize = 4096;
        $file = fopen($source, 'rb');
        $zp = gzopen($destination, 'w9');

        if (!$file || !$zp) {
            return false;
        }

        while (!feof($file)) {
            gzwrite($zp, fread($file, $bufferSize));
        }

        fclose($file);
        gzclose($zp);

        return true;
    }
}