<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);
$password = trim($_POST['password'] ?? '');

if (!$login || empty($password)) {
    header('Location: index.php?error=1');
    exit;
}

try {
    // 1. Ищем пользователя
    $stmt = $cn->prepare('SELECT id, password FROM users WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->bind_result($userId, $hash);
    $found = $stmt->fetch();
    $stmt->close();

    // 2. Проверка пароля (ошибка данных, а не системы)
    if (!$found || !password_verify($password, $hash)) {
        $cn->close();
        header('Location: index.php?error=1');
        exit;
    }

    // 3. Обновляем токен
    $token = bin2hex(random_bytes(15));
    $upd = $cn->prepare('UPDATE users SET token = ? WHERE id = ?');
    $upd->bind_param('si', $token, $userId);
    $upd->execute();
    $upd->close();
    $cn->close();

    // 4. Ставим куки
    setcookie('user_id', (string)$userId, time() + 86400, '/', '', false, true);
    setcookie('token', $token, time() + 86400, '/', '', false, true);

    header('Location: profile.php');
    exit;

} catch (mysqli_sql_exception $e) {
    // Ловим ошибки БД
    error_log('Login error: ' . $e->getMessage());
    if (isset($cn)) $cn->close();
    handl_error('Ошибка при авторизации. Попробуйте позже.', $e->getMessage());
}