<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BackupLog;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    protected $disk = 'local';

    protected $backupFolder = 'backups';

    protected function createMySqlCnf(string $dbHost, string $dbPort, string $dbUser, string $dbPass): string
    {
        $cnfFile = tempnam(sys_get_temp_dir(), 'mysql');
        file_put_contents($cnfFile, sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n",
            $dbUser,
            $dbPass,
            $dbHost,
            $dbPort
        ));
        chmod($cnfFile, 0600);

        return $cnfFile;
    }

    protected function getDbConfig(): array
    {
        return [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'user' => env('DB_USERNAME', 'root'),
            'pass' => env('DB_PASSWORD', ''),
            'name' => env('DB_DATABASE', 'master_data'),
        ];
    }

    protected function runMysqldump(string $backupPath): void
    {
        $db = $this->getDbConfig();
        $cnfFile = $this->createMySqlCnf($db['host'], $db['port'], $db['user'], $db['pass']);

        try {
            $cmd = sprintf(
                'mysqldump --defaults-extra-file=%s --no-tablespaces %s | gzip > %s',
                escapeshellarg($cnfFile),
                escapeshellarg($db['name']),
                escapeshellarg($backupPath)
            );

            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                if (file_exists($backupPath)) {
                    unlink($backupPath);
                }
                throw new \Exception('MySQL backup failed. Return code: '.$returnVar);
            }
        } finally {
            if (file_exists($cnfFile)) {
                unlink($cnfFile);
            }
        }
    }

    protected function runMysqlRestore(string $sqlGzPath): void
    {
        $db = $this->getDbConfig();
        $cnfFile = $this->createMySqlCnf($db['host'], $db['port'], $db['user'], $db['pass']);

        try {
            $cmd = sprintf(
                'gunzip -c %s | mysql --defaults-extra-file=%s %s',
                escapeshellarg($sqlGzPath),
                escapeshellarg($cnfFile),
                escapeshellarg($db['name'])
            );

            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('MySQL restore failed. Return code: '.$returnVar);
            }
        } finally {
            if (file_exists($cnfFile)) {
                unlink($cnfFile);
            }
        }
    }

    public function create($remark = null)
    {
        $connection = config('database.default');

        $extension = $connection === 'mysql' ? '.sql.gz' : '.sqlite.gz';
        $filename = 'backup-'.Carbon::now()->format('Y-m-d-H-i-s').$extension;
        $backupDir = Storage::disk($this->disk)->path($this->backupFolder);
        $backupPath = $backupDir.'/'.$filename;

        if (! file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        if ($connection === 'mysql') {
            $this->runMysqldump($backupPath);
        } else {
            $dbPath = database_path('database.sqlite');

            if (! file_exists($dbPath)) {
                throw new \Exception('SQLite database file not found.');
            }

            $tempPath = $backupDir.'/temp_backup.sqlite';
            copy($dbPath, $tempPath);

            $fp = fopen($tempPath, 'rb');
            $gz = gzopen($backupPath, 'wb9');
            while (! feof($fp)) {
                gzwrite($gz, fread($fp, 8192));
            }
            fclose($fp);
            gzclose($gz);
            unlink($tempPath);
        }

        $fileSize = filesize($backupPath);

        BackupLog::create([
            'filename' => $filename,
            'path' => $this->backupFolder.'/'.$filename,
            'disk' => $this->disk,
            'size' => $fileSize,
            'remark' => $remark,
            'created_by' => Auth::check() ? Auth::user()->name : 'System/Scheduler',
        ]);

        return $filename;
    }

    public function list()
    {
        return BackupLog::latest()->get();
    }

    public function restore($filename)
    {
        $path = Storage::disk($this->disk)->path($this->backupFolder.'/'.$filename);

        if (! file_exists($path)) {
            throw new \Exception('Backup file not found.');
        }

        $this->restoreFromPath($path);

        return true;
    }

    public function restoreFromFile(UploadedFile $file)
    {
        $tempPath = Storage::disk($this->disk)->path('temp_restore_'.time().'.gz');
        $file->move(dirname($tempPath), basename($tempPath));

        $this->validateGzipFile($tempPath);

        $this->restoreFromPath($tempPath, true);

        return true;
    }

    protected function validateGzipFile(string $path): void
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new \Exception('Cannot open uploaded file for validation.');
        }

        $magic = fread($handle, 2);
        fclose($handle);

        if ($magic !== "\x1f\x8b") {
            unlink($path);
            throw new \Exception('Invalid file: uploaded file is not a valid gzip archive.');
        }

        $inode = gzopen($path, 'rb');
        if (! $inode) {
            unlink($path);
            throw new \Exception('Invalid file: cannot decompress the uploaded file.');
        }

        $buffer = gzread($inode, 1024);
        gzclose($inode);

        if ($buffer === false || $buffer === '') {
            unlink($path);
            throw new \Exception('Invalid file: decompressed content is empty or unreachable.');
        }
    }

    protected function restoreFromPath($path, $deleteAfter = false)
    {
        $connection = config('database.default');

        if ($connection === 'mysql') {
            $this->runMysqlRestore($path);
        } else {
            $dbPath = database_path('database.sqlite');

            $tempPath = database_path('restore_temp.sqlite');

            $gz = gzopen($path, 'rb');
            $fp = fopen($tempPath, 'wb');
            while (! gzeof($gz)) {
                fwrite($fp, gzread($gz, 8192));
            }
            gzclose($gz);
            fclose($fp);

            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
            rename($tempPath, $dbPath);
        }

        if ($deleteAfter && file_exists($path)) {
            unlink($path);
        }
    }

    public function delete($filename)
    {
        $path = $this->backupFolder.'/'.$filename;

        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }

        BackupLog::where('filename', $filename)->delete();

        return true;
    }

    public function download($filename)
    {
        $path = $this->backupFolder.'/'.$filename;
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->download($path);
        }

        return null;
    }

    public function deleteBatch(array $filenames): int
    {
        $deleted = 0;
        foreach ($filenames as $filename) {
            try {
                $this->delete($filename);
                $deleted++;
            } catch (\Exception $e) {
            }
        }

        return $deleted;
    }

    public function prune(int $keepDaily = 7, int $keepWeekly = 4, int $keepMonthly = 6): array
    {
        $backups = BackupLog::orderByDesc('created_at')->get();

        $keepSet = [];
        $dailyCounts = [];
        $weeklyCounts = [];
        $monthlyCounts = [];

        foreach ($backups as $backup) {
            $date = $backup->created_at;
            $dayKey = $date->format('Y-m-d');
            $weekKey = $date->format('Y-W');
            $monthKey = $date->format('Y-m');
            $keep = false;

            if (! isset($dailyCounts[$dayKey])) {
                $dailyCounts[$dayKey] = 0;
            }
            if ($dailyCounts[$dayKey] < 1 && count($dailyCounts) <= $keepDaily) {
                $keep = true;
                $dailyCounts[$dayKey]++;
            }

            if (! isset($weeklyCounts[$weekKey])) {
                $weeklyCounts[$weekKey] = 0;
            }
            if ($weeklyCounts[$weekKey] < 1 && count($weeklyCounts) <= $keepWeekly) {
                $keep = true;
                $weeklyCounts[$weekKey]++;
            }

            if (! isset($monthlyCounts[$monthKey])) {
                $monthlyCounts[$monthKey] = 0;
            }
            if ($monthlyCounts[$monthKey] < 1 && count($monthlyCounts) <= $keepMonthly) {
                $keep = true;
                $monthlyCounts[$monthKey]++;
            }

            if ($keep) {
                $keepSet[$backup->filename] = true;
            }
        }

        $deleted = [];
        foreach ($backups as $backup) {
            if (! isset($keepSet[$backup->filename])) {
                try {
                    $this->delete($backup->filename);
                    $deleted[] = $backup->filename;
                } catch (\Exception $e) {
                }
            }
        }

        return ['kept' => count($keepSet), 'deleted' => count($deleted)];
    }
}
