<?php
session_start();

// Массив пользователей (можно заменить на базу данных)
$users = [
    'user' => ['password' => 'userpass', 'role' => 'user'],
    'admin' => ['password' => 'adminpass', 'role' => 'admin']
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка пользователя
    if (isset($users[$username]) && $users[$username]['password'] == $password) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $users[$username]['role'];

        // Перенаправление в зависимости от роли
        if ($_SESSION['role'] == 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = "Неверное имя пользователя или пароль!";
        header('Location: login.php');
        exit;
    }
}
?>
