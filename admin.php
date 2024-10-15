<?php
session_start();

// Проверка, что пользователь авторизован и имеет роль 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Путь к конфигурационному файлу
$configFile = 'config.json';
$templateFile = 'template.php';

// Загрузка текущей конфигурации из файла config.json или установка значений по умолчанию
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : ['limit' => 30, 'interval' => 20, 'destination' => 'syuda'];

// Обработка сохранения изменений в конфигурацию и шаблон
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обновление конфигурации на основе пользовательских данных
    $config['limit'] = intval($_POST['limit']);
    $config['interval'] = intval($_POST['interval']);
    $config['destination'] = $_POST['destination'];

    // Сохранение конфигурации в файл config.json
    file_put_contents($configFile, json_encode($config));

    // Обновление значений шаблона
    $channel = $_POST['channel'];
    $callerid = $_POST['callerid'];
    $waitTime = $_POST['waitTime'];
    $maxRetries = $_POST['maxRetries'];
    $retryTime = $_POST['retryTime'];
    $application = $_POST['application'];
    $data = $_POST['data'];

    // Обновляем файл template.php на основе введенных данных
    $templateContent = "Channel: {$channel}\n";
    $templateContent .= "Callerid: {$callerid}\n";
    $templateContent .= "WaitTime: {$waitTime}\n";
    $templateContent .= "MaxRetries: {$maxRetries}\n";
    $templateContent .= "RetryTime: {$retryTime}\n";
    $templateContent .= "Application: {$application}\n";
    $templateContent .= "Data: {$data}\n";
    $templateContent .= "AlwaysDelete: Yes\n";  // Если этот параметр всегда должен оставаться неизменным

    // Сохраняем изменения в template.php
    file_put_contents($templateFile, $templateContent);

    echo "<p>Изменения сохранены!</p>";
}

// Чтение текущих значений из template.php
$template = file_exists('template.php') ? file_get_contents('template.php') : '';

// Разбор значений из шаблона
preg_match('/Channel: (.+)/', $template, $channelMatches);
preg_match('/Callerid: (.+)/', $template, $calleridMatches);
preg_match('/WaitTime: (.+)/', $template, $waitTimeMatches);
preg_match('/MaxRetries: (.+)/', $template, $maxRetriesMatches);
preg_match('/RetryTime: (.+)/', $template, $retryTimeMatches);
preg_match('/Application: (.+)/', $template, $applicationMatches);
preg_match('/Data: (.+)/', $template, $dataMatches);

$channel = isset($channelMatches[1]) ? $channelMatches[1] : 'Local/{{number}}@indebtedness-notify/n';
$callerid = isset($calleridMatches[1]) ? $calleridMatches[1] : '781500000';
$waitTime = isset($waitTimeMatches[1]) ? $waitTimeMatches[1] : '50';
$maxRetries = isset($maxRetriesMatches[1]) ? $maxRetriesMatches[1] : '{{attempts}}';
$retryTime = isset($retryTimeMatches[1]) ? $retryTimeMatches[1] : '900';
$application = isset($applicationMatches[1]) ? $applicationMatches[1] : 'Playback';
$data = isset($dataMatches[1]) ? $dataMatches[1] : 'neutral/notify/freelinkuzru';

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style/styles1.css">
</head>
<body>
    <div class="container">
        <a href="login.php" class="button-back">⏎</a>
        <header>
            <h1>Управление шаблоном и конфигурацией</h1>
        </header>
        
        <form method="POST" action="admin.php">
            <!-- Редактирование параметров шаблона -->
            <h2>Параметры шаблона</h2>
            
            <label for="channel">Channel:</label>
            <input type="text" id="channel" name="channel" value="<?php echo htmlspecialchars($channel); ?>">

            <label for="callerid">Callerid (Укажите внешний номер):</label>
            <input type="text" id="callerid" name="callerid" value="<?php echo htmlspecialchars($callerid); ?>">

            <label for="waitTime">WaitTime (Укажите время ожидания ответа):</label>
            <input type="number" id="waitTime" name="waitTime" value="<?php echo htmlspecialchars($waitTime); ?>">

            <label for="maxRetries">MaxRetries:</label>
            <input type="text" id="maxRetries" name="maxRetries" value="<?php echo htmlspecialchars($maxRetries); ?>">

            <label for="retryTime">RetryTime (Укажите время повтора):</label>
            <input type="number" id="retryTime" name="retryTime" value="<?php echo htmlspecialchars($retryTime); ?>">

            <label for="application">Application (выбор из queue, playback, Dial):</label>
            <select id="application" name="application">
                <option value="Playback" <?php if ($application == 'Playback') echo 'selected'; ?>>Playback</option>
                <option value="queue" <?php if ($application == 'queue') echo 'selected'; ?>>Queue</option>
                <option value="Dial" <?php if ($application == 'Dial') echo 'selected'; ?>>Dial</option>
            </select>

            <label for="data">Data (Укажите контекст):</label>
            <input type="text" id="data" name="data" value="<?php echo htmlspecialchars($data); ?>">

            <h2>Конфигурация переносов файлов</h2>
            
            <label for="limit">Количество файлов для переноса за раз:</label>
            <input type="number" id="limit" name="limit" value="<?php echo htmlspecialchars($config['limit']); ?>">

            <label for="interval">Интервал времени между переносами (в секундах):</label>
            <input type="number" id="interval" name="interval" value="<?php echo htmlspecialchars($config['interval']); ?>">

            <label for="destination">Путь к папке назначения:</label>
            <input type="text" id="destination" name="destination" value="<?php echo htmlspecialchars($config['destination']); ?>">

            <input type="submit" value="Сохранить изменения">
        </form>
    </div>
</body>
</html>
