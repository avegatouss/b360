<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\BackupLog;

class DatabaseBackup extends Command
{
    protected $signature = 'core:backup {--type=scheduled : Type of backup (manual|scheduled)}';

    protected $description = 'Create a database backup and log it';

    private const BACKUP_DIR = 'backups';

    public function handle(): int
    {
        $this->info('Starting database backup...');

        $type = $this->option('type');

        try {
            $config = config('database.connections.' . config('database.default'));
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'] ?? '';

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';

            if (!Storage::exists(self::BACKUP_DIR)) {
                Storage::makeDirectory(self::BACKUP_DIR);
            }

            $fullPath = Storage::path(self::BACKUP_DIR . '/' . $filename);

            $command = sprintf(
                'mysqldump -h %s -P %s -u %s %s %s --single-transaction --routines --triggers > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );

            $result = Process::run($command);

            if (!$result->successful() || !file_exists($fullPath) || filesize($fullPath) === 0) {
                $this->warn('mysqldump failed, using PHP fallback...');
                $this->phpDump($fullPath, $database);
            }

            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            BackupLog::create([
                'filename' => $filename,
                'size_bytes' => $size,
                'type' => $type,
                'status' => 'success',
                'notes' => 'Backup via commande artisan core:backup',
                'created_by' => null,
                'created_at' => now(),
            ]);

            $this->info("Backup created: {$filename} ({$this->formatSize($size)})");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            BackupLog::create([
                'filename' => 'failed_' . date('Y-m-d_His') . '.sql',
                'size_bytes' => 0,
                'type' => $type,
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'created_by' => null,
                'created_at' => now(),
            ]);

            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function phpDump(string $fullPath, string $database): void
    {
        $connection = DB::connection(config('database.default'));
        $tables = $connection->select('SHOW TABLES');
        $key = 'Tables_in_' . $database;

        $sql = "-- B360 Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key ?? array_values((array) $table)[0];

            $createResult = $connection->select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createResult[0]->{'Create Table'} ?? '';
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createSql . ";\n\n";

            $rows = $connection->select("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $values = array_map(function ($v) use ($connection) {
                        if ($v === null) return 'NULL';
                        return $connection->getPdo()->quote($v);
                    }, (array) $row);
                    $sql .= "INSERT INTO `{$tableName}` VALUES(" . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($fullPath, $sql);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' Ko';
        return $bytes . ' octets';
    }
}
