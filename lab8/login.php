<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 1. Фильтрация данных формы
$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);
$password = trim($_POST['password'] ?? ''); // Убираем лишние пробелы

if (!$login || empty($password)) {
    header('Location: index.php?error=1');
    exit;
}

// 2. Безопасный запрос (Prepared Statement)
$stmt = $cn->prepare('SELECT id, password FROM users WHERE login = ?');
$stmt->bind_param('s', $login);
$stmt->execute();
$stmt->bind_result($userId, $hash);
$found = $stmt->fetch();
$stmt->close();

// Проверка пароля
if (!$found || !password_verify($password, $hash)) {
    $cn->close();
    header('Location: index.php?error=1');
    exit;
}

// 3. Создаем новый токен при входе (Prepared Statement)
$token = bin2hex(random_bytes(15));
$upd = $cn->prepare('UPDATE users SET token = ? WHERE id = ?');
$upd->bind_param('si', $token, $userId);
$upd->execute();
$upd->close();
$cn->close();

// 4. Ставим куки (HttpOnly = true для защиты от XSS)
setcookie('user_id', (string)$userId, time() + 86400, '/', '', false, true);
setcookie('token', $token, time() + 86400, '/', '', false, true);

header('Location: profile.php');
exit;