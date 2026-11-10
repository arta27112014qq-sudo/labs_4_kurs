<?php
// Безопасно получаем сообщения из GET-запроса
$error_message = isset($_GET['error_message']) ? htmlspecialchars(urldecode($_GET['error_message'])) : 'Произошла неизвестная ошибка.';
$system_error = isset($_GET['system_error_message']) ? htmlspecialchars(urldecode($_GET['system_error_message'])) : 'Технические детали отсутствуют.';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ошибка — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Зелёный двор</h1>
</header>
<main>
    <div class="card error-card">
        <h2 style="color: #c0392b;">Произошла ошибка</h2>
        
        <div class="error-message">
            <p>Уважаемый пользователь!</p>
            <p>Наша система не смогла обработать Ваше последнее действие. Мы уже в курсе проблемы и предпримем все возможные действия, чтобы Вас не огорчать.</p>
            <br>
            <p><strong>Что случилось:</strong> <?= $error_message ?></p>
        </div>

        <div class="system-error-box">
            <p><strong>Технические детали (только для разработчика):</strong></p>
            <code><?= $system_error ?></code>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="javascript:history.go(-1)" class="btn-submit" style="text-decoration:none; display:inline-block;">← Вернуться назад</a>
        </div>
    </div>
</main>
</body>
</html>