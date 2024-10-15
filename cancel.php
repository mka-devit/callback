<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
        header('Location: login.php');
        exit;
    }

    function deleteFilesInDirectory($directory, $extension = '*') {
        $files = glob("$directory/*.$extension");

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                for ($i = 0; $i < 5; $i++) {
                    clearstatcache();
                    if (!file_exists($file)) {
                        break;
                    }
                    usleep(500000);
                    unlink($file);
                }
            }
        }
    }

    function clearLatestLogFile() {
        $logFiles = glob('processed_logs/*.log');
        if ($logFiles) {
            usort($logFiles, fn($a, $b) => filemtime($b) - filemtime($a));
            $latestLogFile = $logFiles[0];
            file_put_contents($latestLogFile, '');
            for ($i = 0; $i < 5; $i++) {
                clearstatcache();
                if (filesize($latestLogFile) === 0) {
                    break;
                }
                usleep(500000);
                file_put_contents($latestLogFile, '');
            }
        }
    }

    deleteFilesInDirectory('create', 'call');
    deleteFilesInDirectory('upload');
    clearLatestLogFile();

    $_SESSION['files_deleted'] = true;
    echo 'OK';
    exit;
}
?>
