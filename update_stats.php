<?php
header('Content-Type: application/json');

function getLastLogFileLineCount() {
    $logFiles = glob('processed_logs/*.log');
    return $logFiles ? count(file($logFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : 0;
}

function getCreateDirectoryFileCount() {
    return count(glob('create/*.call'));
}

echo json_encode([
    'lastLogFileLineCount' => getLastLogFileLineCount(),
    'createDirectoryFileCount' => getCreateDirectoryFileCount()
]);
?>
