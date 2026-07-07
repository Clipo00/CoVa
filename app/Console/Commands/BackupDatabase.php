<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--disk=local}';
    protected $description = 'Backup the database using mysqldump and gzip';

    private const RETENTION_DAYS = 7;

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "covar_backup_{$timestamp}.sql.gz";
        $filepath = "{$backupDir}/{$filename}";

        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', 'forge');
        $username = env('DB_USERNAME', 'forge');
        $password = env('DB_PASSWORD', '');

        $this->info("Starting database backup for {$database}@{$host}...");

        $dumpCommand = sprintf(
            'mysqldump --no-tablespaces --single-transaction --quick --lock-tables=false -h %s -P %s -u %s -p%s %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath),
        );

        $result = Process::run($dumpCommand);

        if (! $result->successful()) {
            $this->error("mysqldump failed: {$result->errorOutput()}");
            Log::error("Database backup failed", ['error' => $result->errorOutput()]);

            return self::FAILURE;
        }

        $size = $this->formatBytes((int) filesize($filepath));
        $this->info("Backup created: {$filename} ({$size})");
        Log::info("Database backup created", ['file' => $filename, 'size' => $size]);

        // Rotate old backups
        $this->rotateBackups($backupDir);

        return self::SUCCESS;
    }

    private function rotateBackups(string $backupDir): void
    {
        $files = glob("{$backupDir}/covar_backup_*.sql.gz");

        if ($files === false || $files === []) {
            return;
        }

        $cutoff = now()->subDays(self::RETENTION_DAYS)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            $mtime = (int) filemtime($file);
            if ($mtime < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Rotated {$deleted} old backup(s).");
            Log::info("Backup rotation completed", ['deleted' => $deleted]);
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
