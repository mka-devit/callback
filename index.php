<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

function logProcessedNumbers($numbers) {
    $logDir = 'processed_logs';
    $date = date('Y-m-d_H');
    $logFile = "$logDir/$date.log";

    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $numbers = array_values(array_unique(array_filter($numbers)));
    file_put_contents($logFile, implode("\n", $numbers) . "\n", FILE_APPEND | LOCK_EX);

    $file_content = array_unique(array_filter(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
    sort($file_content);
    file_put_contents($logFile, implode("\n", $file_content) . "\n");
}

function getLastProcessedNumbers() {
    $logFiles = glob('processed_logs/*.log');
    if ($logFiles) {
        usort($logFiles, fn($a, $b) => filemtime($b) - filemtime($a));
        return array_filter(array_unique(file($logFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
    }
    return [];
}

function getBlockedNumbers($offset = 0, $limit = 10) {
    $blockedNumbers = getAllBlockedNumbers();
    sort($blockedNumbers); // Сортируем массив по возрастанию
    return array_slice($blockedNumbers, $offset, $limit);
}

function getAllBlockedNumbers() {
    $blockedNumbers = file_exists('blocked/blocked.txt') 
        ? array_filter(array_unique(file('blocked/blocked.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))) 
        : [];
    sort($blockedNumbers); // Сортируем массив по возрастанию
    return $blockedNumbers;
}

function addBlockedNumber($number) {
    $blockedNumbers = getAllBlockedNumbers();
    if (!in_array($number, $blockedNumbers)) {
        $blockedNumbers[] = $number;
        sort($blockedNumbers);
        file_put_contents('blocked/blocked.txt', implode("\n", $blockedNumbers) . "\n");
        updateBannedNumbers($blockedNumbers);
    }
}

function removeBlockedNumber($number) {
    $blockedNumbers = array_diff(getAllBlockedNumbers(), [$number]);
    sort($blockedNumbers);
    file_put_contents('blocked/blocked.txt', implode("\n", $blockedNumbers) . "\n");
    updateBannedNumbers($blockedNumbers);
}

function getBannedNumbers() {
    return file_exists('blocked/ban.txt') 
        ? array_filter(array_unique(file('blocked/ban.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)))
        : [];
}

function updateBannedNumbers($blockedNumbers = null) {
    $blockedNumbers = $blockedNumbers ?? getAllBlockedNumbers();
    $bannedNumbers = getBannedNumbers();
    $updatedBannedNumbers = array_unique(array_merge($blockedNumbers, array_diff($bannedNumbers, $blockedNumbers)));
    sort($updatedBannedNumbers);
    file_put_contents('blocked/ban.txt', implode("\n", $updatedBannedNumbers) . "\n");
}

function getLastLogFileLineCount() {
    $logFiles = glob('processed_logs/*.log');
    if ($logFiles) {
        usort($logFiles, fn($a, $b) => filemtime($b) - filemtime($a));
        $lastFile = $logFiles[0];
        $lineCount = count(file($lastFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        // Отладочный вывод для логов
        error_log("Последний лог файл: " . $lastFile);
        error_log("Количество строк в файле: " . $lineCount);

        return $lineCount;
    }

    // Отладочный вывод, если логов нет
    error_log("Лог файлы не найдены.");
    return 0;
}

function getCreateDirectoryFileCount() {
    return count(glob('create/*.call'));
}

updateBannedNumbers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['new_number'])) {
        addBlockedNumber(trim($_POST['new_number']));
    }

    if (!empty($_POST['number_to_remove'])) {
        removeBlockedNumber(trim($_POST['number_to_remove']));
    }

    $blockedNumbers = getAllBlockedNumbers();
    $lastProcessedNumbers = getLastProcessedNumbers();

    if (!empty($_FILES['file']['name'])) {
        if (pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) == 'txt') {
            $timestamp = date('Y-m-d_H');
            $uploadedFilePath = "temp/$timestamp.txt";
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadedFilePath);

            $numbers = array_diff(array_unique(array_filter(file($uploadedFilePath, FILE_IGNORE_NEW_LINES))), $blockedNumbers, $lastProcessedNumbers);
            sort($numbers);
            $uploadFileName = "upload/$timestamp.txt";
            file_put_contents($uploadFileName, implode("\n", $numbers));
            rename($uploadedFilePath, $uploadFileName);

            logProcessedNumbers($numbers);

            // Генерация .call файлов на основе загруженных номеров
            $template = file_get_contents("template.php");
            foreach ($numbers as $number) {
                $callFileContent = str_replace("{{number}}", $number, $template);
                file_put_contents("create/$number.call", $callFileContent);
            }

            echo "<script>showNotification('Файл $uploadFileName обработан, создано " . count($numbers) . " .call файлов!');</script>";
        } else {
            echo "<script>showNotification('Только файлы .txt разрешены!');</script>";
        }
    }

    // Обработка диапазона номеров
    if (isset($_POST['start_range'], $_POST['end_range']) && is_numeric($_POST['start_range']) && is_numeric($_POST['end_range'])) {
        $start = intval($_POST['start_range']);
        $end = intval($_POST['end_range']);

        // Ограничение на разницу между началом и концом диапазона (не более 10 000)
        if ($end - $start > 10000) {
            echo "<script>showNotification('Максимальный диапазон не может превышать 10 000 номеров!');</script>";
        } else {
            $numbers = range($start, $end);
            $uniqueNumbers = array_diff($numbers, $blockedNumbers, $lastProcessedNumbers);
            sort($uniqueNumbers);

            $fileName = "upload/range_{$start}_to_{$end}.txt";
            file_put_contents($fileName, implode("\n", $uniqueNumbers));
            logProcessedNumbers($uniqueNumbers);

            $template = file_get_contents("template.php");
            foreach ($uniqueNumbers as $number) {
                $callFileContent = str_replace("{{number}}", $number, $template);
                file_put_contents("create/$number.call", $callFileContent);
            }

            echo "<script>showNotification('Диапазон номеров сохранен в $fileName и .call файлы созданы!');</script>";
        }
    }
}
function getSystemStatus() {
    $statusFile = 'asterisk/status.txt';
    
    if (file_exists($statusFile)) {
        $status = trim(file_get_contents($statusFile));
        return $status === 'played=yes' ? 'Работает' : 'Пауза';
    }
    
    return 'Статус неизвестен';
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Метаданные и ссылки на стили -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница Пользователя</title>
    <!-- Подключаем шрифт -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <!-- Подключаем ваш файл стилей -->
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <div class="container">
        <div class="back-button">
            <a href="login.php">⏎</a>
        </div>

        <!-- Секция статистики -->
        <section class="statistics-section">
            <h2><br>Статистика:</h2>
            <p>Загружено: <?php echo getLastLogFileLineCount(); ?></p>
            <p>Осталось: <?php echo getCreateDirectoryFileCount(); ?></p>
            <p>Статус: <span id="status"><?php echo getSystemStatus(); ?></span></p>
        </section>

        <!-- Остальной контент вашей страницы -->
        <header>
            <!-- Здесь может быть ваш заголовок -->
        </header>

        <section class="upload-section">
            <h2>Конвертация файлов:</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Выберите файл для загрузки (только *.txt):</label>
                <input type="file" name="file" accept=".txt">
                <label>Начальный диапазон:</label>
                <input type="text" name="start_range" maxlength="9" pattern="\d{9}">
                <label>Конечный диапазон:</label>
                <input type="text" name="end_range" maxlength="9" pattern="\d{9}">
                <button type="submit">Старт</button>
                <!-- Кнопка отмены -->
                <button type="button" id="cancelButton">Отменить</button>
            </form>
        </section>

        <!-- Кнопка для паузы/запуска без перезагрузки -->
        <button type="button" id="stopButton">Пауза/Запуск</button>

        <section class="block-section">
            <h2>Заблокированные номера:</h2>
            <form method="POST">
                <label>Добавить номер:</label>
                <input type="text" name="new_number" maxlength="15" pattern="\d{1,15}">
                <button type="submit">Добавить</button>
            </form>
            <form method="POST">
                <label>Удалить номер:</label>
                <input type="text" name="number_to_remove" maxlength="15" pattern="\d{1,15}">
                <button type="submit">Удалить</button>
            </form>
        </section>

        <section class="blocked-numbers-section">
            <h2>Заблокированные номера:</h2>
            <ul>
                <?php
                $limit = 10; // Количество номеров на одной странице
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset = ($page - 1) * $limit;

                $blockedNumbers = getBlockedNumbers($offset, $limit);
                if (!empty($blockedNumbers)) {
                    foreach ($blockedNumbers as $number) {
                        echo "<li>" . htmlspecialchars($number) . "</li>";
                    }
                } else {
                    echo "<p>Номера отсутствуют</p>";
                }
                ?>
            </ul>
            <div class="pagination">
                <?php
                $totalBlockedNumbers = getAllBlockedNumbers(); // Получаем все заблокированные номера
                $totalPages = ceil(count($totalBlockedNumbers) / $limit); // Вычисляем общее количество страниц
                $pageRange = 3; // Количество страниц для показа вокруг текущей

                // Ссылка на первую страницу и предыдущую
                if ($page > 1) {
                    echo '<a href="?page=1" class="pagination-button">&laquo;</a>';
                    echo '<a href="?page=' . ($page - 1) . '" class="pagination-button">&laquo;</a>';
                }

                // Показ диапазона страниц (вокруг текущей страницы)
                for ($i = max(1, $page - $pageRange); $i <= min($totalPages, $page + $pageRange); $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-button active">' . $i . '</span>';
                    } else {
                        echo '<a href="?page=' . $i . '" class="pagination-button">' . $i . '</a>';
                    }
                }

                // Ссылка на следующую и последнюю страницу
                if ($page < $totalPages) {
                    echo '<a href="?page=' . ($page + 1) . '" class="pagination-button"> &raquo;</a>';
                    echo '<a href="?page=' . $totalPages . '" class="pagination-button"> &raquo;</a>';
                }
                ?>
            </div>
        </section>
    </div>

    <!-- Окно логов -->
    <section class="logs-section">
        <h2>Логи:</h2>
        <div id="logs-container"></div>
    </section>

<!-- Подключение внешних JavaScript-файлов -->
<script src="scriptjs/notifications.js"></script>
<script src="scriptjs/cancel.js"></script>
<script src="scriptjs/startCall.js"></script>
<script src="scriptjs/updateStatistics.js"></script>
<script src="scriptjs/updateRemainingFiles.js"></script>
<script src="scriptjs/updateLogs.js"></script>
<script src="scriptjs/buttons.js"></script>
<script src="scriptjs/init.js"></script>
</body>
</html>
