<?php
// check_login.php
require_once 'error_handler.php'; // Подключаем обработчик ошибок
require 'connect.php';

// Устанавливаем заголовок, что мы возвращаем JSON
header('Content-Type: application/json');

// Получаем логин из POST-запроса
$login = filter_input(INPUT_POST, 'login', FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^[A-Za-z0-9]{3,50}$/']]);

// Если логин не прошел валидацию (например, короткий или содержит кириллицу)
if (!$login) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный логин']);
    exit;
}

try {
    // Проверяем в базе данных, есть ли такой логин (Prepared Statement)
    $stmt = $cn->prepare('SELECT id FROM users WHERE login = ?');
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $stmt->store_result();

    // Если найдено больше 0 строк — логин занят
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'taken', 'message' => 'Логин уже занят']);
    } else {
        // Иначе — свободен
        echo json_encode(['status' => 'free', 'message' => 'Логин свободен']);
    }
    
    $stmt->close();
    $cn->close();

} catch (mysqli_sql_exception $e) {
    // Если произошла ошибка БД, возвращаем JSON с ошибкой
    error_log('AJAX DB error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера']);
}
?>