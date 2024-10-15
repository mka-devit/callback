<?php
$status_file = 'asterisk/status.txt';
$current_status = file_get_contents($status_file);

if ($current_status === 'played=yes') {
    file_put_contents($status_file, 'played=no');
    echo "<script>alert('Скрипт остановлен!');</script>";
} else {
    file_put_contents($status_file, 'played=yes');
    echo "<script>alert('Скрипт запущен!');</script>";
}

// Возврат на главную страницу
header('Location: index.php');
exit;
?>
