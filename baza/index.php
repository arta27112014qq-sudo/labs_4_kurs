<?php
session_start();
if (isset($_COOKIE['user_login'])) {
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

        <form action="login.php" method="POST">
            <label>Логин</label>
            <input type="text" name="login" required placeholder="Ваш логин">
            
            <label>Пароль</label>
            <input type="password" name="password" required placeholder="••••••••">
            
            <button type="submit">Войти</button>
        </form>

        <div class="links">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</main>
</body>
</html>