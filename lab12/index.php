<?php
session_start();
if (isset($_COOKIE['user_id']) && isset($_COOKIE['token'])) {
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Зелёный двор</h1>
    <p>Войдите, чтобы делать покупки</p>
</header>
<main>
    <div class="card">
        <h2>Вход</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">Неверный логин или пароль!</div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #f0faf4; color: #27ae60; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #bce2c8;">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label>Логин</label>
            <input type="text" name="login" required placeholder="Ваш логин">
            
            <label>Пароль</label>
            <input type="password" name="password" required placeholder="••••••••">
            
            <button type="submit">Войти</button>
        </form>

        <div class="links" style="display: flex; flex-direction: column; gap: 10px;">
            <div>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></div>
            <div><a href="forgot_password.php">Забыли пароль?</a></div>
        </div>
    </div>
</main>
</body>
</html>