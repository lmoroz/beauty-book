<?php

declare(strict_types=1);

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class SnapshotController extends Controller
{
    private const EXCLUDED_TABLES = [
        'migration',
        'source_message',
        'message',
    ];

    private function getSnapshotDir(): string
    {
        return Yii::getAlias('@app/runtime/snapshots');
    }

    private function getSqlPath(): string
    {
        return $this->getSnapshotDir() . '/latest.sql';
    }

    private function getUploadsBackupPath(): string
    {
        return $this->getSnapshotDir() . '/uploads';
    }

    private function getUploadsPath(): string
    {
        return Yii::getAlias('@app/web/uploads');
    }

    private function getDbParams(): array
    {
        $dsn = Yii::$app->db->dsn;
        preg_match('/host=([^;]+)/', $dsn, $hostMatch);
        preg_match('/dbname=([^;]+)/', $dsn, $dbMatch);

        return [
            'host' => $hostMatch[1] ?? 'localhost',
            'dbname' => $dbMatch[1] ?? 'beautybook',
            'user' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'sslFlag' => $this->getSslFlag(),
        ];
    }

    private function getSslFlag(): string
    {
        $check = shell_exec('mysql --help 2>&1 | grep -c skip-ssl');
        return (int) trim($check ?: '0') > 0 ? '--skip-ssl' : '';
    }

    public function actionCreate(): int
    {
        $this->stdout("Creating database snapshot...\n");

        $dir = $this->getSnapshotDir();
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->stderr("ERROR: Cannot create directory: {$dir}\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }
        @chmod($dir, 0777);

        $db = $this->getDbParams();
        $sqlPath = $this->getSqlPath();

        $ignoreTables = '';
        foreach (self::EXCLUDED_TABLES as $table) {
            $ignoreTables .= ' --ignore-table=' . escapeshellarg($db['dbname'] . '.' . $table);
        }

        $errFile = $sqlPath . '.err';
        $cmd = sprintf(
            'mysqldump -h %s -u %s -p%s %s --single-transaction --routines --triggers %s %s > %s 2>%s',
            escapeshellarg($db['host']),
            escapeshellarg($db['user']),
            escapeshellarg($db['password']),
            $db['sslFlag'],
            $ignoreTables,
            escapeshellarg($db['dbname']),
            escapeshellarg($sqlPath),
            escapeshellarg($errFile)
        );

        shell_exec($cmd);
        if (file_exists($errFile)) {
            $errors = trim(file_get_contents($errFile));
            @unlink($errFile);
            if ($errors !== '') {
                $this->stderr("mysqldump warnings: {$errors}\n");
            }
        }

        if (!file_exists($sqlPath) || filesize($sqlPath) === 0) {
            $this->stderr("ERROR: Snapshot file is empty or was not created\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $size = round(filesize($sqlPath) / 1024, 1);
        @chmod($sqlPath, 0666);
        $this->stdout("Database snapshot saved: {$sqlPath} ({$size} KB)\n");

        $uploadsPath = $this->getUploadsPath();
        $uploadsBackup = $this->getUploadsBackupPath();

        if (is_dir($uploadsPath)) {
            if (is_dir($uploadsBackup)) {
                $this->removeDirectory($uploadsBackup);
            }
            $this->copyDirectory($uploadsPath, $uploadsBackup);
            $this->fixPermissions($uploadsBackup);
            $this->stdout("Uploads backed up to: {$uploadsBackup}\n");
        } else {
            $this->stdout("No uploads directory found, skipping uploads backup\n");
        }

        $this->stdout('Snapshot completed at ' . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }

    public function actionRestore(): int
    {
        $sqlPath = $this->getSqlPath();
        if (!file_exists($sqlPath)) {
            $this->stderr("ERROR: No snapshot found at {$sqlPath}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Restoring database from snapshot...\n");

        $db = $this->getDbParams();

        $tables = $this->getRestorableTables($db);
        if (!empty($tables)) {
            $dropSql = 'SET FOREIGN_KEY_CHECKS=0; ';
            foreach ($tables as $table) {
                $dropSql .= "DROP TABLE IF EXISTS `{$table}`; ";
            }
            $dropSql .= 'SET FOREIGN_KEY_CHECKS=1;';

            $dropCmd = sprintf(
                'mysql -h %s -u %s -p%s %s %s -e %s 2>&1',
                escapeshellarg($db['host']),
                escapeshellarg($db['user']),
                escapeshellarg($db['password']),
                $db['sslFlag'],
                escapeshellarg($db['dbname']),
                escapeshellarg($dropSql)
            );

            $output = shell_exec($dropCmd);
            if ($output !== null && $output !== '') {
                $this->stderr("Drop tables output: {$output}\n");
            }
        }

        $cmd = sprintf(
            'mysql -h %s -u %s -p%s %s %s < %s 2>&1',
            escapeshellarg($db['host']),
            escapeshellarg($db['user']),
            escapeshellarg($db['password']),
            $db['sslFlag'],
            escapeshellarg($db['dbname']),
            escapeshellarg($sqlPath)
        );

        $output = shell_exec($cmd);
        if ($output !== null && $output !== '') {
            $this->stderr("mysql import output: {$output}\n");
        }

        $this->stdout("Database restored from snapshot\n");

        $uploadsBackup = $this->getUploadsBackupPath();
        $uploadsPath = $this->getUploadsPath();

        if (is_dir($uploadsBackup)) {
            if (is_dir($uploadsPath)) {
                $this->removeDirectory($uploadsPath);
            }
            $this->copyDirectory($uploadsBackup, $uploadsPath);
            $this->fixPermissions($uploadsPath);
            $this->stdout("Uploads restored from backup\n");
        }

        Yii::$app->cache->flush();
        $this->stdout("Cache flushed\n");

        $this->stdout('Restore completed at ' . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }

    public function actionAutoReset(): int
    {
        $this->stdout("Checking auto-reset setting...\n");

        $salon = \app\models\Salon::find()->one();
        if ($salon === null) {
            $this->stderr("ERROR: No salon found\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $settings = $salon->getSettingsArray();
        $enabled = isset($settings['auto_reset_enabled']) && $settings['auto_reset_enabled'];

        if (!$enabled) {
            $this->stdout("Auto-reset is disabled. Exiting.\n");
            return ExitCode::OK;
        }

        $sqlPath = $this->getSqlPath();
        if (!file_exists($sqlPath)) {
            $this->stderr("ERROR: Auto-reset enabled but no snapshot found\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Auto-reset is enabled. Restoring...\n");
        return $this->actionRestore();
    }

    private function getRestorableTables(array $db): array
    {
        $cmd = sprintf(
            'mysql -h %s -u %s -p%s %s %s -N -e "SHOW TABLES" 2>/dev/null',
            escapeshellarg($db['host']),
            escapeshellarg($db['user']),
            escapeshellarg($db['password']),
            $db['sslFlag'],
            escapeshellarg($db['dbname'])
        );

        $output = shell_exec($cmd);
        if ($output === null || trim($output) === '') {
            return [];
        }

        $allTables = array_filter(array_map('trim', explode("\n", $output)));
        return array_filter($allTables, function ($table) {
            return !in_array($table, self::EXCLUDED_TABLES, true);
        });
    }

    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0775, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    private function fixPermissions(string $dir): void
    {
        @chmod($dir, 0777);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            @chmod($item->getPathname(), $item->isDir() ? 0777 : 0666);
        }
    }
}
