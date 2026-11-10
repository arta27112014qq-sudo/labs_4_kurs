<?php
/**
 * Функция перенаправляет пользователя на страницу ошибки.
 * @param string $error_message Понятное пользователю сообщение.
 * @param string $system_error_message Технические детали для отладки.
 */
function handl_error($error_message, $system_error_message) {
    // Кодируем параметры, чтобы они безопасно передались в URL
    $err = urlencode($error_message);
    $sys_err = urlencode($system_error_message);
    
    // Перенаправляем на страницу ошибки
    header("Location: error.php?error_message={$err}&system_error_message={$sys_err}");
    exit();
}
?>