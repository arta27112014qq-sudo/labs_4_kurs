<?php
$name = htmlspecialchars($_POST['first_name'] ?? '');
$surname = htmlspecialchars($_POST['last_name'] ?? '');
$login = htmlspecialchars($_POST['login'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$phone = htmlspecialchars($_POST['phone'] ?? '');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-list { list-style: none; margin-top: 20px; }
        .profile-list li { padding: 10px 0; border-bottom: 1px solid #f0e6ec; font-size: 15px; }
        .profile-list span { font-weight: bold; color: #9c4f76; }
    </style>
</head>
<body>
<div class="card">
    <h2>Успешно!</h2>
    <p style="text-align:center; color:#8ccf8c; font-weight:bold; margin-bottom:20px;">Вы зарегистрированы</p>
    
    <ul class="profile-list">
        <li><span>Имя:</span> <?= $name ?></li>
        <li><span>Фамилия:</span> <?= $surname ?></li>
        <li><span>Логин:</span> <?= $login ?></li>
        <li><span>E-mail:</span> <?= $email ?></li>
        <li><span>Телефон:</span> <?= $phone ?></li>
    </ul>
    
    <br>
    <a href="register.php" style="display:block; text-align:center; color:#9c4f76; font-weight:bold; text-decoration:none;">← Назад</a>
</div>
</body>
</html>