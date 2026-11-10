<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Зелёный двор</h1>
</header>
<main>
    <div class="card">
        <h2>Восстановление пароля</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #f0faf4; color: #27ae60; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #bce2c8;">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">Пользователь с такой почтой не найден.</div>
        <?php endif; ?>

        <form action="send_reset.php" method="POST">
            <div class="form-group">
                <label>Введите ваш E-mail:</label>
                <input type="email" name="mail" placeholder="ivan@mail.ru" required>
            </div>
            <button type="submit" class="btn-submit">Сбросить пароль</button>
        </form>
        
        <div class="links">
            <a href="index.php">← Вернуться на главную</a>
        </div>
    </div>
</main>
</body>
</html>