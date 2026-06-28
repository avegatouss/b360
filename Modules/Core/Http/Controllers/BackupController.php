<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\BackupLog;
use Modules\Core\Support\CurrentInstance;

final class BackupController extends Controller
{
    private const BACKUP_DIR = 'backups';

    /**
     * List existing backups.
     */
    public function index()
    {
        $instance = CurrentInstance::get();

        $backups = BackupLog::orderByDesc('created_at')->paginate(20);

        // Also scan the directory for any files not in DB
        $files = collect();
        if (Storage::exists(self::BACKUP_DIR)) {
            $files = collect(Storage::files(self::BACKUP_DIR))
                ->map(fn ($path) => [
                    'filename' => basename($path),
                    'size' => Storage::size($path),
                    'modified' => Storage::lastModified($path),
                ])
                ->sortByDesc('modified')
                ->values();
        }

        return view('core::admin.backups', compact('instance', 'backups', 'files'));
    }

    /**
     * Create a new database backup.
     */
    public function create(Request $request)
    {
        $instance = CurrentInstance::get();

        try {
            $filename = $this->performBackup();

            $size = Storage::exists(self::BACKUP_DIR.'/'.$filename)
                ? Storage::size(self::BACKUP_DIR.'/'.$filename)
                : 0;

            BackupLog::create([
                'filename' => $filename,
                'size_bytes' => $size,
                'type' => 'manual',
                'status' => 'success',
                'notes' => 'Backup manuel via interface',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            return back()->with('status', "Backup cree avec succes : {$filename}");
        } catch (\Throwable $e) {
            BackupLog::create([
                'filename' => 'failed_'.date('Y-m-d_His').'.sql',
                'size_bytes' => 0,
                'type' => 'manual',
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            return back()->with('error', 'Echec du backup : '.$e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download(string $slug, string $filename)
    {
        $path = self::BACKUP_DIR.'/'.basename($filename);

        if (! Storage::exists($path)) {
            abort(404, 'Fichier de backup introuvable.');
        }

        return Storage::download($path, $filename);
    }

    /**
     * Restore from a backup file.
     */
    public function restore(Request $request, string $slug, string $filename)
    {
        $instance = CurrentInstance::get();
        $path = self::BACKUP_DIR.'/'.basename($filename);

        if (! Storage::exists($path)) {
            return back()->with('error', 'Fichier de backup introuvable.');
        }

        try {
            $fullPath = Storage::path($path);
            $config = config('database.connections.'.config('database.default'));

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'] ?? '';

            $command = sprintf(
                'mysql -h %s -P %s -u %s %s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '-p'.escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );

            $result = Process::run($command);

            if ($result->successful()) {
                return back()->with('status', "Base de donnees restauree depuis {$filename}.");
            }

            return back()->with('error', 'Echec de la restauration : '.$result->errorOutput());
        } catch (\Throwable $e) {
            return back()->with('error', 'Echec de la restauration : '.$e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request, string $slug, string $filename)
    {
        $path = self::BACKUP_DIR.'/'.basename($filename);

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        BackupLog::where('filename', basename($filename))->delete();

        return back()->with('status', "Backup {$filename} supprime.");
    }

    /**
     * Perform the actual database backup using mysqldump.
     */
    private function performBackup(): string
    {
        $config = config('database.connections.'.config('database.default'));

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $filename = 'backup_'.date('Y-m-d_His').'.sql';

        if (! Storage::exists(self::BACKUP_DIR)) {
            Storage::makeDirectory(self::BACKUP_DIR);
        }

        $fullPath = Storage::path(self::BACKUP_DIR.'/'.$filename);

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s --single-transaction --routines --triggers > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '-p'.escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($fullPath)
        );

        $result = Process::run($command);

        if (! $result->successful()) {
            // Fallback: use PHP-based SQL dump
            $this->phpDump($fullPath, $database);
        }

        if (! file_exists($fullPath) || filesize($fullPath) === 0) {
            // Try PHP dump as ultimate fallback
            $this->phpDump($fullPath, $database);
        }

        return $filename;
    }

    /**
     * PHP-based SQL dump fallback when mysqldump is unavailable.
     */
    private function phpDump(string $fullPath, string $database): void
    {
        $connection = DB::connection(config('database.default'));
        $tables = $connection->select('SHOW TABLES');
        $key = 'Tables_in_'.$database;

        $sql = "-- B360 Database Backup\n";
        $sql .= '-- Generated: '.date('Y-m-d H:i:s')."\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key ?? array_values((array) $table)[0];

            // Table structure
            $createResult = $connection->select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createResult[0]->{'Create Table'} ?? '';
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createSql.";\n\n";

            // Table data
            $rows = $connection->select("SELECT * FROM `{$tableName}`");
            if (! empty($rows)) {
                foreach ($rows as $row) {
                    $values = array_map(function ($v) use ($connection) {
                        if ($v === null) {
                            return 'NULL';
                        }

                        return $connection->getPdo()->quote($v);
                    }, (array) $row);
                    $sql .= "INSERT INTO `{$tableName}` VALUES(".implode(',', $values).");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($fullPath, $sql);
    }
}
