<?php
session_start();
require 'connect.php';

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
$avatarPath = NULL; // По умолчанию аватарки нет
$fileUploaded = false;

// Проверяем, был ли вообще загружен файл (ошибка 4 = файл не выбран)
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];
    
    // Ошибки загрузки (см. документацию PHP)
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Ошибка при загрузке файла на сервер (код: ' . $file['error'] . ')';
    } 
    // Проверка размера (2 Мб = 2097152 байт)
    elseif ($file['size'] > 2097152) {
        $errors[] = 'Размер аватарки не должен превышать 2 Мб.';
    } 
    else {
        // Проверка, что это действительно картинка (а не PHP-скрипт)
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
    // 3. СОХРАНЕНИЕ В БД
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
    $token = bin2hex(random_bytes(15));

    // Вставляем пользователя (пока без аватарки)
    $stmt = $cn->prepare('INSERT INTO users (login, password, name, mail, token) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $login, $hash, $name, $mail, $token);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    // 4. РАБОТА С ФАЙЛОМ
    if ($fileUploaded) {
        // Создаем папку uploads, если её нет
        $uploadBaseDir = __DIR__ . '/uploads';
        if (!is_dir($uploadBaseDir)) {
            mkdir($uploadBaseDir, 0777, true);
        }

        // Создаем папку для конкретного пользователя (как просили: своя папка для каждого)
        $userDirName = 'user_' . $userId;
        $userDirPath = $uploadBaseDir . '/' . $userDirName;
        if (!is_dir($userDirPath)) {
            mkdir($userDirPath, 0777, true);
        }

        // Формируем уникальное имя файла (avatar.jpg или avatar.png)
        $extension = ($imageInfo['mime'] === 'image/png') ? '.png' : '.jpg';
        $fileName = 'avatar' . $extension;
        $fullPath = $userDirPath . '/' . $fileName;
        
        // Путь для сохранения в БД (относительно корня сайта)
        $avatarPath = 'uploads/' . $userDirName . '/' . $fileName;

        // Перемещаем файл из временной папки
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Не удалось переместить загруженный файл.");
        }

        // Обновляем запись в БД, добавляя путь к аватарке
        $upd = $cn->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        $upd->bind_param('si', $avatarPath, $userId);
        $upd->execute();
        $upd->close();
    }

    $cn->close();

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