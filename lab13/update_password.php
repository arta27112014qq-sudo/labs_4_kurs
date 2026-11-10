<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token = filter_input(INPUT_POST, 'token', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[0-9a-f]{60}$/']]);
$password = trim($_POST['password'] ?? '');

if (!$token || strlen($password) < 8) {
    $_SESSION['errors'] = ['Пароль должен быть не менее 8 символов.'];
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Обновляем пароль только если токен совпадает, и сразу обнуляем токен
    $stmt = $cn->prepare('UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?');
    $stmt->bind_param('ss', $hash, $token);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $cn->close();
        $_SESSION['success'] = 'Пароль успешно изменён. Теперь вы можете войти.';
        header('Location: index.php');
        exit;
    } else {
        $stmt->close();
        $cn->close();
        handl_error('Ошибка обновления пароля', 'Токен не найден или уже использован.');
    }
} catch (mysqli_sql_exception $e) {
    if (isset($cn)) $cn->close();
    handl_error('Ошибка при обновлении пароля.', $e->getMessage());
}