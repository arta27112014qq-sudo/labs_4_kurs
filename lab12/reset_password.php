<?php
session_start();
require 'connect.php';

$token = filter_input(INPUT_GET, 'token', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[0-9a-f]{60}$/']]);

$isValidToken = false;
$userId = null;

if ($token) {
    try {
        $stmt = $cn->prepare('SELECT id FROM users WHERE reset_token = ?');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->bind_result($userId);
        if ($stmt->fetch()) {
            $isValidToken = true;
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if (isset($cn)) $cn->close();
        handl_error('Ошибка при проверке токена.', $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новый пароль — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Зелёный двор</h1></header>
<main>
    <div class="card">
        <h2>Новый пароль</h2>
        
        <?php if (!$isValidToken): ?>
            <div class="error">Ссылка недействительна или устарела.</div>
            <a href="forgot_password.php" class="btn-submit" style="display: block; text-decoration: none; text-align: center;">Запросить новую ссылку</a>
        <?php else: ?>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="error">
                    <?php foreach ($_SESSION['errors'] as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="update_password.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>Придумайте новый пароль:</label>
                    <input type="password" name="password" placeholder="••••••••" required minlength="8">
                </div>
                <button type="submit" class="btn-submit">Сохранить пароль</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>