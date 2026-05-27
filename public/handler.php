<?php
// public/handler.php
// Единая точка входа для вызова активностей из Битрикс24

header('Content-Type: application/json; charset=utf-8');

// Логгируем входящие данные для отладки (можно смотреть в файле или выводить)
$input = file_get_contents('php://input');
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'raw_input' => $input,
    'post' => $_POST,
    'get' => $_GET
];

// Временно пишем лог в файл, чтобы видеть, что присылает Б24
file_put_contents(__DIR__ . '/handler_log.txt', json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

// Парсим данные (Б24 обычно шлет JSON в POST)
$data = json_decode($input, true) ?? $_POST;

// Получаем код активности (он должен передаваться в параметрах)
$activityCode = $data['ACTIVITY_CODE'] ?? $data['code'] ?? 'unknown';

// === МИНИМАЛЬНАЯ ЛОГИКА (ЗАГЛУШКА) ===
// В будущем здесь будет роутинг: ActivityRouter::handle($activityCode, $data);

$response = [
    'result' => 'OK',
    'message' => "Activity '{$activityCode}' received successfully (MVP Stub).",
    'received_data' => $data
];

echo json_encode($response);