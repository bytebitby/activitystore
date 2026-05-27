<?php
// index.php - Точка входа

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitrix Activity Store</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; line-height: 1.6; }
        .status { color: green; font-weight: bold; }
        a { color: #007bff; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 2rem; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Bitrix Activity Store (MVP)</h1>
        <p>Статус сервера: <span class="status">Работает</span></p>
        <p>Версия ядра: 0.1.0</p>
        <hr>
        <h3>Доступные эндпоинты:</h3>
        <ul>
            <li><a href="/marketplace.html" target="_blank">🛒 Витрина активностей</a></li>
            <li><a href="/api/market_list.php" target="_blank">📋 API: Список активностей</a></li>
            <li><a href="/spike_test.php" target="_blank">🧪 SPIKE: Тест регистрации (Proof of Concept)</a></li>
        </ul>
        <p style="margin-top: 2rem; font-size: 0.9em; color: #666;">
            Следующий шаг: Запустить <b>spike_test.php</b> для проверки возможности динамической регистрации активности в Bitrix24.
        </p>
    </div>
</body>
</html>