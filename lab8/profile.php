<?php
session_start();
require 'connect.php';

// 1. Получаем куки
$id = filter_input(INPUT_COOKIE, 'user_id', FILTER_VALIDATE_INT);
$token = filter_input(INPUT_COOKIE, 'token', FILTER_VALIDATE_REGEXP, [
    'options' => ['regexp' => '/^[0-9a-f]{30}$/']
]);

$isAuthorized = false;
$userName = '';
$userMail = '';
$userLogin = '';

// 2. Если куки есть, проверяем их в базе данных (Prepared Statement)
if ($id && $token) {
    $stmt = $cn->prepare('SELECT name, mail, login FROM users WHERE id = ? AND token = ?');
    $stmt->bind_param('is', $id, $token);
    $stmt->execute();
    $stmt->bind_result($userName, $userMail, $userLogin);
    
    if ($stmt->fetch()) {
        $isAuthorized = true;
    }
    $stmt->close();
}
$cn->close();

// 3. Если не авторизован — выкидываем на главную
if (!$isAuthorized) {
    header('Location: index.php');
    exit;
}

// 4. Обработка выхода из системы
if (isset($_GET['logout'])) {
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('token', '', time() - 3600, '/');
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Зелёный двор</h1>
    <p>Ваш личный кабинет</p>
</header>
<main>
    <div class="card profile-info">
        <h3>Привет, <?= htmlspecialchars($userName) ?>!</h3>
        <p>Вы успешно вошли в систему.</p>
        
        <ul class="profile-list" style="list-style: none; margin-top: 20px;">
            <li style="padding: 10px 0; border-bottom: 1px solid #f0e6ec;">
                <span style="font-weight: bold; color: #9c4f76;">Логин:</span> 
                <?= htmlspecialchars($userLogin) ?>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #f0e6ec;">
                <span style="font-weight: bold; color: #9c4f76;">E-mail:</span> 
                <?= htmlspecialchars($userMail) ?>
            </li>
        </ul>
        
        <a href="?logout=1" style="display:inline-block; margin-top:20px; background:#c0392b; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;">
            Выйти
        </a>
    </div>
</main>
</body>
</html>