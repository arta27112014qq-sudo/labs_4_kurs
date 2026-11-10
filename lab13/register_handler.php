<?php
session_start();
require 'connect.php';
require 'mailer.php'; // Подключаем функцию отправки почты

$errors = [];

// 1. ФИЛЬТРАЦИЯ ТЕКСТОВЫХ ДАННЫХ
$name = filter_input(INPUT_POST, 'name', FILTER_VALIDATE_REGEXP, 
    ['options' => ['regexp' => '/^[А-Яа-яЁёA-Za-z\s]{2,100}$/u']]);
if (!$name) $errors[] = 'Имя: только буквы и пробел (от 2 до 100 символов)';
else $name = trim($name);

$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP, 
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);
if (!$login) $errors[] = 'Логин: только латиница и цифры (от 3 до 50 символов)';

$mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
if (!$mail) $errors[] = 'Некорректный E-mail';

$password = trim($_POST['password'] ?? ''); 
if (strlen($password) < 8) $errors[] = 'Пароль должен быть не менее 8 символов';

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: register.php');
    exit;
}

// 2. ПРОВЕРКА ФАЙЛА
$avatarPath = NULL; 
$fileUploaded = false;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Ошибка при загрузке файла (код: ' . $file['error'] . ')';
    } elseif ($file['size'] > 2097152) {
        $errors[] = 'Размер аватарки не должен превышать 2 Мб.';
    } else {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Загруженный файл не является изображением.';
        } else {
            $mime = $imageInfo['mime'];
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
                $errors[] = 'Разрешены только форматы JPEG и PNG.';
            } else {
                $fileUploaded = true;
            }
        }
    }
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: register.php');
    exit;
}

try {
    // Проверка на дубликат логина
    $stmt = $cn->prepare('SELECT id FROM users WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $cn->close();
        $_SESSION['errors'] = ['Такой логин уже занят'];
        header('Location: register.php');
        exit;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(15)); // Токен для авторизации (куки)
    $emailToken = bin2hex(random_bytes(30)); // Токен для подтверждения почты (в письме)

    // Вставляем пользователя
    $stmt = $cn->prepare('INSERT INTO users (login, password, name, mail, token, check_email, reset_token) VALUES (?, ?, ?, ?, ?, 0, ?)');
    $stmt->bind_param('ssssss', $login, $hash, $name, $mail, $token, $emailToken);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    // --- ОТПРАВКА ПИСЬМА С ПОДТВЕРЖДЕНИЕМ ---
    $subject = 'Регистрация на сайте «Зелёный двор»';
    $link = "http://" . $_SERVER['HTTP_HOST'] . "/check_email.php?token=" . $emailToken;
    
    $message = "
    <html>
    <head><title>Подтверждение регистрации</title></head>
    <body style='font-family: Arial, sans-serif; color: #2b3327;'>
        <h2 style='color: #2f4a26;'>Добро пожаловать в «Зелёный двор»!</h2>
        <p>Для завершения регистрации, пожалуйста, подтвердите свой адрес электронной почты.</p>
        <p><a href='{$link}' style='display:inline-block; padding: 10px 20px; background: #2f4a26; color: white; text-decoration: none; border-radius: 5px;'>Подтвердить почту</a></p>
        <p>Если кнопка не работает, скопируйте ссылку: <br> {$link}</p>
        <br>
        <p>С уважением,<br>Администрация сайта</p>
    </body>
    </html>";

    sendEmail($mail, $subject, $message);
    // ----------------------------------------

    // Работа с файлом (аватаркой)
    if ($fileUploaded) {
        $uploadBaseDir = __DIR__ . '/uploads';
        if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);
        $userDirName = 'user_' . $userId;
        $userDirPath = $uploadBaseDir . '/' . $userDirName;
        if (!is_dir($userDirPath)) mkdir($userDirPath, 0777, true);

        $extension = ($imageInfo['mime'] === 'image/png') ? '.png' : '.jpg';
        $fileName = 'avatar' . $extension;
        $fullPath = $userDirPath . '/' . $fileName;
        $avatarPath = 'uploads/' . $userDirName . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Не удалось переместить загруженный файл.");
        }

        $upd = $cn->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        $upd->bind_param('si', $avatarPath, $userId);
        $upd->execute();
        $upd->close();
    }

    $cn->close();

    // Авторизация
    setcookie('user_id', (string)$userId, time() + 86400, '/', '', false, true);
    setcookie('token', $token, time() + 86400, '/', '', false, true);

    header('Location: profile.php');
    exit;

} catch (mysqli_sql_exception $e) {
    error_log('Registration DB error: ' . $e->getMessage());
    if (isset($cn)) $cn->close();
    handl_error('Произошла ошибка при сохранении данных.', $e->getMessage());
} catch (Exception $e) {
    error_log('File upload error: ' . $e->getMessage());
    if (isset($cn)) $cn->close();
    handl_error('Ошибка при загрузке файла.', $e->getMessage());
}