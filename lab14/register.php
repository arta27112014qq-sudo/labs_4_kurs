<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация — Зелёный двор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h2>Регистрация</h2>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="error">
            <?php foreach ($_SESSION['errors'] as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="register_handler.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Имя и Фамилия:</label>
            <input type="text" name="name" placeholder="Иван Иванов" required minlength="2" pattern="^[А-Яа-яЁёA-Za-z\s]+$">
        </div>

        <div class="form-group">
            <label>Логин:</label>
            <!-- Добавлен id и oninput для отслеживания ввода -->
            <input type="text" name="login" id="login_input" placeholder="qwerty" required minlength="3" pattern="^[A-Za-z0-9]+$" oninput="checkLogin(this.value)">
            <!-- Блок для вывода результата AJAX -->
            <div id="login_status" class="status-box"></div>
        </div>

        <div class="form-group">
            <label>Пароль:</label>
            <input type="password" name="password" placeholder="••••••••" required minlength="8">
        </div>

        <div class="form-group">
            <label>E-mail:</label>
            <input type="email" name="mail" placeholder="ivan@mail.ru" required>
        </div>

        <div class="form-group">
            <label>Аватар (необязательно):</label>
            <input type="file" name="avatar" accept="image/jpeg, image/png, image/jpg">
            <small class="hint">Формат JPEG или PNG, размер до 2 Мб</small>
        </div>

        <button type="submit" class="btn-submit" id="submit_btn">ЗАРЕГИСТРИРОВАТЬСЯ</button>
    </form>
</div>

<script>
    // Переменная для задержки (debounce), чтобы не спамить сервер при каждом нажатии клавиши
    let timeout = null;

    function checkLogin(login) {
        const statusBox = document.getElementById('login_status');
        const submitBtn = document.getElementById('submit_btn');

        // Очищаем предыдущий таймер
        clearTimeout(timeout);

        // Если поле пустое, убираем сообщение
        if (login.length === 0) {
            statusBox.innerHTML = '';
            submitBtn.disabled = false;
            return;
        }

        // Ждем 500 мс после последнего нажатия клавиши, затем делаем запрос
        timeout = setTimeout(async () => {
            statusBox.innerHTML = '<span style="color: #6b7a63;">Проверка...</span>';
            submitBtn.disabled = true; // Блокируем кнопку отправки на время проверки

            // Подготавливаем данные для отправки
            const formData = new FormData();
            formData.append('login', login);

            try {
                // AJAX запрос через Fetch API
                const response = await fetch('check_login.php', {
                    method: 'POST',
                    body: formData
                });

                // Парсим JSON-ответ от сервера
                const result = await response.json();

                if (result.status === 'free') {
                    statusBox.innerHTML = '<span style="color: #27ae60;">Логин свободен</span>';
                    submitBtn.disabled = false; // Разблокируем кнопку
                } else if (result.status === 'taken') {
                    statusBox.innerHTML = '<span style="color: #c0392b;">Логин уже занят</span>';
                    submitBtn.disabled = true; // Кнопка остается заблокированной
                } else {
                    statusBox.innerHTML = `<span style="color: #c0392b;">⚠ ${result.message}</span>`;
                    submitBtn.disabled = true;
                }
            } catch (error) {
                console.error('Ошибка AJAX:', error);
                statusBox.innerHTML = '<span style="color: #c0392b;">⚠ Ошибка соединения с сервером</span>';
                submitBtn.disabled = true;
            }
        }, 500); // Задержка 500 миллисекунд
    }
</script>
</body>
</html>