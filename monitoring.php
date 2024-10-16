<?php
$directory = '/var/www/html/syuda';

if (is_dir($directory)) {
    $files = array_diff(scandir($directory), array('..', '.'));
    
    // Сортировка файлов по времени изменения (последние сверху)
    usort($files, function($a, $b) use ($directory) {
        return filemtime($directory . '/' . $b) - filemtime($directory . '/' . $a);
    });

    echo '<ul>';
    foreach ($files as $file) {
        echo '<li>' . htmlspecialchars($file) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>Директория не найдена.</p>';
}
?>
