<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup-run';

    protected $description = 'Create a timestamped compressed backup of the database';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $sqlFileName = "backup-zippy-{$timestamp}.sql";
        $zipFileName = "backup-zippy-{$timestamp}.zip";
        $sqlFilePath = $backupDir . DIRECTORY_SEPARATOR . $sqlFileName;
        $zipFilePath = $backupDir . DIRECTORY_SEPARATOR . $zipFileName;

        $handle = fopen($sqlFilePath, 'w');
        if (!$handle) {
            $this->error('Failed to open backup SQL file for writing.');
            return 1;
        }

        $pdo = DB::getPdo();

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone=\"+00:00\";\n\n");

        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $tableObj) {
            $tableArray = (array) $tableObj;
            $tableName = reset($tableArray);

            if (empty($tableName)) {
                continue;
            }

            $createResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (empty($createResult)) {
                continue;
            }

            $createArray = (array) $createResult[0];
            $createSql = $createArray['Create Table'] ?? (array_values($createArray)[1] ?? null);

            if (!$createSql) {
                continue;
            }

            fwrite($handle, "\nDROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createSql . ";\n\n");

            $batch = [];
            $cols = [];

            $rows = DB::table($tableName)->cursor();

            foreach ($rows as $row) {
                $rowArr = (array) $row;
                if (empty($cols)) {
                    $cols = array_map(function ($col) {
                        return "`{$col}`";
                    }, array_keys($rowArr));
                }

                $values = [];
                foreach ($rowArr as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($val)) {
                        $values[] = $val;
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }

                $batch[] = '(' . implode(', ', $values) . ')';

                if (count($batch) >= 150) {
                    fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }

            if (!empty($batch) && !empty($cols)) {
                fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES\n" . implode(",\n", $batch) . ";\n");
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($sqlFilePath, $sqlFileName);
            $zip->close();
            @unlink($sqlFilePath);
        } else {
            $this->error('Failed to create compressed zip archive.');
            return 1;
        }

        $files = glob($backupDir . DIRECTORY_SEPARATOR . '*.zip');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file) >= 86400 * 30)) {
                @unlink($file);
            }
        }

        $size = round(filesize($zipFilePath) / (1024 * 1024), 2);
        $this->info("Database backup created successfully: {$zipFileName} ({$size} MB)");

        return 0;
    }
}
