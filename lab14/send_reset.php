<?php
session_start();
require 'connect.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

$mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);

if (!$mail) {
    header('Location: forgot_password.php?error=1');
    exit;
}

try {
    // Ищем пользователя по почте
    $stmt = $cn->prepare('SELECT id, name FROM users WHERE mail = ?');
    $stmt->bind_param('s', $mail);
    $stmt->execute();
    $stmt->bind_result($userId, $userName);
    $found = $stmt->fetch();
    $stmt->close();

    if ($found) {
        // Генерируем токен сброса
        $resetToken = bin2hex(random_bytes(30));
        
        $upd = $cn->prepare('UPDATE users SET reset_token = ? WHERE id = ?');
        $upd->bind_param('si', $resetToken, $userId);
        $upd->execute();
        $upd->close();

        // Отправляем письмо
        $subject = 'Сброс пароля на сайте «Зелёный двор»';
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $resetToken;
        
        $message = "
        <html>
        <head><title>Сброс пароля</title></head>
        <body style='font-family: Arial, sans-serif; color: #2b3327;'>
            <h2 style='color: #2f4a26;'>Здравствуйте, {$userName}!</h2>
            <p>Вы запросили сброс пароля на сайте «Зелёный двор».</p>
            <p>Для создания нового пароля перейдите по ссылке:</p>
            <p><a href='{$link}' style='display:inline-block; padding: 10px 20px; background: #2f4a26; color: white; text-decoration: none; border-radius: 5px;'>Придумать новый пароль</a></p>
            <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
            <br>
            <p>С уважением,<br>Администрация сайта</p>
        </body>
        </html>";

        sendEmail($mail, $subject, $message);
    }

    $cn->close();

    // Всегда показываем успех, даже если почта не найдена (защита от перебора юзеров)
    $_SESSION['success'] = 'Если такая почта зарегистрирована, письмо с инструкциями было отправлено.';
    header('Location: forgot_password.php');
    exit;

} catch (mysqli_sql_exception $e) {
    error_log('Reset pass DB error: ' . $e->getMessage());
    if (isset($cn)) $cn->close();
    handl_error('Произошла ошибка при сбросе пароля.', $e->getMessage());
}