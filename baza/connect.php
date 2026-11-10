<?php
// connect.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ВНИМАНИЕ: Замените эти данные на те, что выдал вам хостинг (сделай.site)
$host = 'localhost'; 
$user = 'nasida_nasida'; // Например: nasida_nasida
$pass = '27112014QwQ'; 
$db   = 'nasida_nasida';

try {
    $cn = new mysqli($host, $user, $pass, $db);
    $cn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('DB connect error: ' . $e->getMessage());
    exit('Ошибка подключения к базе данных. Попробуйте позже.');
}