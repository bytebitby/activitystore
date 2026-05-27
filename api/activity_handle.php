<?php

file_put_contents(
    __DIR__ . '/../storage/activity_debug.txt',
    print_r([
        '_GET' => $_GET,
        '_POST' => $_POST,
        '_REQUEST' => $_REQUEST,
        'RAW' => file_get_contents('php://input')
    ], true)
);

header('Content-Type: application/json');

echo json_encode([
    'result' => [
        'success' => true
    ]
]);
/**
 * Единый обработчик вызовов активностей от Bitrix24.
 * Получает код активности из параметра и делегирует обработку роутеру.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRouter;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

header('Content-Type: application/json');

// Проверка метода запроса (Bitrix24 обычно использует POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получение кода активности из параметров запроса
$activityCode = $_GET['code'] ?? $_POST['code'] ?? null;

if (!$activityCode) {
    http_response_code(400);
    echo json_encode(['error' => 'Не указан код активности']);
    exit;
}

try {
    // Инициализация клиента Bitrix24 и менеджера состояния
    $bitrixClient = new BitrixClient();
    $stateManager = new ActivityStateManager($bitrixClient);
    
    // Создание роутера и обработка вызова
    $router = new ActivityRouter($stateManager);
    
    // Получение параметров от Bitrix24
    $params = array_merge($_GET, $_POST);
    unset($params['code']); // Удаляем код активности из параметров
    
    $result = $router->handle($activityCode, $params);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка обработки: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
