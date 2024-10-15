<?php
header('Content-Type: application/json');

if (!function_exists('getLastLogFileLineCount')) {
    function getLastLogFileLineCount() {
        $logFiles = glob('processed_logs/*.log');
        return $logFiles ? count(file($logFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : 0;
    }
}

if (!function_exists('getCreateDirectoryFileCount')) {
    function getCreateDirectoryFileCount() {
        return count(glob('create/*.call'));
    }
}

if (!function_exists('getStatus')) {
    function getStatus() {
        $statusFile = 'asterisk/status.txt';
        if (file_exists($statusFile)) {
            $status = trim(file_get_contents($statusFile));
            return $status === 'played=yes' ? 'Работает' : 'Пауза';
        }
        return 'Статус недоступен';
    }
}

if (!function_exists('getScriptStatus')) {
    function getScriptStatus() {
        $sourceDir = '/var/www/html/asterisk'; 
        $running = shell_exec("pgrep -f mvtooutgoingdir.sh"); 

        // Если скрипт запущен, возвращаем Online
        if (!empty($running)) {
            return 'Online';
        }

        // Если файлов нет в директории, возвращаем Disabled
        $files = glob($sourceDir . '/*');
        return empty($files) ? 'Disabled' : 'Idle'; // Idle, если скрипт не запущен, но файлы есть
    }
}

echo json_encode([
    'lastLogFileLineCount' => getLastLogFileLineCount(),
    'createDirectoryFileCount' => getCreateDirectoryFileCount(),
    'status' => getStatus(),
    'scriptStatus' => getScriptStatus() 
]);
?>
