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
$stay = isset($_POST['stay']); // Проверяем галочку "Оставаться на сайте"

if (!$login || empty($password)) {
    header('Location: index.php?error=1');
    exit;
}

try {
    $stmt = $cn->prepare('SELECT id, password FROM users WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->bind_result($userId, $hash);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found || !password_verify($password, $hash)) {
        $cn->close();
        header('Location: index.php?error=1');
        exit;
    }

    // Генерируем новый токен при каждом входе
    $token = bin2hex(random_bytes(15));
    $upd = $cn->prepare('UPDATE users SET token = ? WHERE id = ?');
    $upd->bind_param('si', $token, $userId);
    $upd->execute();
    $upd->close();
    $cn->close();

    // ЛОГИКА ВРЕМЕНИ ЖИЗНИ COOKIE
    // Если галочка стоит - 60 дней (3600 * 24 * 60). Иначе - 1 день (86400 сек).
    $cookieTime = $stay ? time() + 3600 * 24 * 60 : time() + 86400;

    setcookie('user_id', (string)$userId, $cookieTime, '/', '', false, true);
    setcookie('token', $token, $cookieTime, '/', '', false, true);

    header('Location: profile.php');
    exit;

} catch (mysqli_sql_exception $e) {
    error_log('Login error: ' . $e->getMessage());
    if (isset($cn)) $cn->close();
    handl_error('Ошибка при авторизации. Попробуйте позже.', $e->getMessage());
}