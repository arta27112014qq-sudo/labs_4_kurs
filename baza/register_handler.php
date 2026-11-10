<?php
session_start();
require 'connect.php';

$errors = [];

// 1. ФИЛЬТРАЦИЯ ДАННЫХ
$name = filter_input(INPUT_POST, 'name', FILTER_VALIDATE_REGEXP, 
    ['options' => ['regexp' => '/^[А-Яа-яЁёA-Za-z\s]{2,100}$/u']]);
if (!$name) $errors[] = 'Имя: только буквы и пробел';

$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP, 
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);
if (!$login) $errors[] = 'Логин: только латиница и цифры';

$mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
if (!$mail) $errors[] = 'Некорректный E-mail';

$password = $_POST['password'] ?? '';
if (strlen($password) < 8) $errors[] = 'Пароль должен быть не менее 8 символов';

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: register.php');
    exit;
}

// 2. ПОДГОТОВЛЕННЫЕ ВЫРАЖЕНИЯ (Prepared Statements)
// Проверяем, нет ли уже такого логина
$stmt = $cn->prepare('SELECT id FROM users WHERE login = ?');
$stmt->bind_param('s', $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $cn->close();
    $_SESSION['errors'] = ['Такой логин уже занят'];
    header('Location: register.php');
    exit;
}
$stmt->close();

// Хешируем пароль
$hash = password_hash($password, PASSWORD_DEFAULT);

// Вставляем нового пользователя
$stmt = $cn->prepare('INSERT INTO users (login, password, name, mail) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $login, $hash, $name, $mail);
$stmt->execute();
$userId = $stmt->insert_id;
$stmt->close();

// Автоматически логиним (для простоты)
$token = bin2hex(random_bytes(15));
$upd = $cn->prepare('UPDATE users SET token = ? WHERE id = ?');
$upd->bind_param('si', $token, $userId);
$upd->execute();
$upd->close();
$cn->close();

setcookie('user_id', (string)$userId, time() + 86400, '/', '', false, true);
setcookie('token', $token, time() + 86400, '/', '', false, true);

header('Location: profile.php');
exit;