<?php
session_start();
require 'connect.php';

// Получаем токен из GET-запроса
$token = filter_input(INPUT_GET, 'token', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[0-9a-f]{60}$/']]);

$success = false;

if ($token) {
    try {
        // Ищем пользователя с таким токеном
        $stmt = $cn->prepare('SELECT id FROM users WHERE reset_token = ? AND check_email = 0');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->bind_result($userId);
        
        if ($stmt->fetch()) {
            $stmt->close();
            // Если нашли — подтверждаем почту и очищаем токен
            $upd = $cn->prepare('UPDATE users SET check_email = 1, reset_token = NULL WHERE id = ?');
            $upd->bind_param('i', $userId);
            $upd->execute();
            $upd->close();
            $success = true;
        } else {
            $stmt->close();
        }
        $cn->close();
    } catch (mysqli_sql_exception $e) {
        error_log('Check email error: ' . $e->getMessage());
        if (isset($cn)) $cn->close();
        handl_error('Ошибка при подтверждении почты.', $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение почты</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Зелёный двор</h1></header>
<main>
    <div class="card" style="text-align: center;">
        <?php if ($success): ?>
            <h2 style="color: #27ae60;">Поздравляем!</h2>
            <p>Ваш адрес электронной почты успешно подтверждён.</p>
        <?php else: ?>
            <h2 style="color: #c0392b;">Ошибка</h2>
            <p>Ссылка недействительна или срок её действия истёк. Возможно, почта уже была подтверждена ранее.</p>
        <?php endif; ?>
        
        <a href="profile.php" class="btn-submit" style="display: inline-block; margin-top: 20px; text-decoration: none;">Перейти в профиль</a>
    </div>
</main>
</body>
</html>