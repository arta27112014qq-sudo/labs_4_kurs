<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 1. ПРОВЕРКА НА БЛОКИРОВКУ
if (isset($_SESSION['block_time']) && $_SESSION['block_time'] > time()) {
    $remaining = ceil(($_SESSION['block_time'] - time()) / 60);
    $_SESSION['error_msg'] = "Слишком много попыток. Вход заблокирован на {$remaining} мин.";
    header('Location: index.php');
    exit;
} elseif (isset($_SESSION['block_time']) && $_SESSION['block_time'] <= time()) {
    // Если время блокировки истекло, сбрасываем его и очищаем историю
    unset($_SESSION['block_time']);
    unset($_SESSION['login_attempts']);
}

$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);
$password = trim($_POST['password'] ?? '');
$stay = isset($_POST['stay']);

if (!$login || empty($password)) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $cn->prepare('SELECT id, password FROM users WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->bind_result($userId, $hash);
    $found = $stmt->fetch();
    $stmt->close();

    // 2. ПРОВЕРКА ПАРОЛЯ И ПОДСЧЕТ ОШИБОК
    if (!$found || !password_verify($password, $hash)) {
        $cn->close();
        
        // Инициализируем массив попыток, если его нет
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        // Добавляем текущее время
        $_SESSION['login_attempts'][] = time();
        
        // Очищаем старые попытки (старше 5 минут = 300 секунд)
        $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($time) {
            return $time > (time() - 300);
        });
        
        // Если попыток 3 и больше - блокируем на 10 минут (600 секунд)
        if (count($_SESSION['login_attempts']) >= 3) {
            $_SESSION['block_time'] = time() + 600; // Блокировка на 10 минут
            $_SESSION['error_msg'] = "3 неудачные попытки. Вход заблокирован на 10 минут.";
        } else {
            $attempts_left = 3 - count($_SESSION['login_attempts']);
            $_SESSION['error_msg'] = "Неверный логин или пароль. Осталось попыток: {$attempts_left}.";
        }
        
        header('Location: index.php');
        exit;
    }

    // 3. УСПЕШНЫЙ ВХОД (сбрасываем счетчики)
    unset($_SESSION['login_attempts']);
    unset($_SESSION['block_time']);

    $token = bin2hex(random_bytes(15));
    $upd = $cn->prepare('UPDATE users SET token = ? WHERE id = ?');
    $upd->bind_param('si', $token, $userId);
    $upd->execute();
    $upd->close();
    $cn->close();

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