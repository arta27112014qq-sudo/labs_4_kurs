<?php
// connect.php
require_once 'error_handler.php'; // Подключаем функцию обработки ошибок

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost'; 
$user = 'nasida_nasida'; 
$pass = '27112014QwQ'; 
$db   = 'nasida_nasida';

try {
    $cn = new mysqli($host, $user, $pass, $db);
    $cn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Логируем ошибку в файл (для админа)
    error_log('DB connect error: ' . $e->getMessage());
    // Перенаправляем пользователя на страницу ошибки
    handl_error('Не удалось подключиться к базе данных.', $e->getMessage());
}   