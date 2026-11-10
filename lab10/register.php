<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h2>Регистрация</h2>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="error">
            <?php foreach ($_SESSION['errors'] as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Обязательно enctype="multipart/form-data" для загрузки файлов! -->
    <form action="register_handler.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Имя и Фамилия:</label>
            <input type="text" name="name" placeholder="Иван Иванов" required minlength="2" pattern="^[А-Яа-яЁёA-Za-z\s]+$">
        </div>

        <div class="form-group">
            <label>Логин:</label>
            <input type="text" name="login" placeholder="qwerty" required minlength="3" pattern="^[A-Za-z0-9]+$">
        </div>

        <div class="form-group">
            <label>Пароль:</label>
            <input type="password" name="password" placeholder="••••••••" required minlength="8">
        </div>

        <div class="form-group">
            <label>E-mail:</label>
            <input type="email" name="mail" placeholder="ivan@mail.ru" required>
        </div>

        <!-- Новое поле для файла -->
        <div class="form-group">
            <label>Аватар (необязательно):</label>
            <input type="file" name="avatar" accept="image/jpeg, image/png, image/jpg">
            <small class="hint">Формат JPEG или PNG, размер до 2 Мб</small>
        </div>

        <button type="submit" class="btn-submit">ЗАРЕГИСТРИРОВАТЬСЯ</button>
    </form>
</div>
</body>
</html>