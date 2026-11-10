<?php
/**
 * Отправляет HTML-письмо.
 * @param string $to Кому
 * @param string $subject Тема
 * @param string $message Тело письма (HTML)
 * @return bool
 */
function sendEmail($to, $subject, $message) {
    // Заголовки для HTML-письма
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: Зелёный двор <no-reply@nasida-nasida.sdelai.site>\r\n"; // Замени на свой домен!
    $headers .= "Reply-To: support@nasida-nasida.sdelai.site\r\n";

    // Отправка
    return mail($to, $subject, $message, $headers);
}
?>