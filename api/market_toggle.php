<?php

/**
 * API эндпоинт для подключения/отключения активности.
 * Вызывается при нажатии кнопки в витрине.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRegistry;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

header('Content-Type: application/json');

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получение данных из запроса
$input = json_decode(file_get_contents('php://input'), true);
$activityCode = $input['activity_code'] ?? null;
$action = $input['action'] ?? null; // 'enable' или 'disable'

if (!$activityCode || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Необходимо указать activity_code и action']);
    exit;
}

// Проверка существования активности в реестре
if (!ActivityRegistry::exists($activityCode)) {
    http_response_code(404);
    echo json_encode(['error' => "Активность '{$activityCode}' не найдена"]);
    exit;
}

try {
    // Инициализация клиента Bitrix24
    $bitrixClient = new BitrixClient();
    $stateManager = new ActivityStateManager($bitrixClient);

    if ($action === 'enable') {
        // Сначала регистрируем активность в Bitrix24 (если еще не зарегистрирована)
        $status = $stateManager->getStatus($activityCode);
        
        if (!$status['registered']) {
            // Регистрация активности через bizproc.activity.add
            $activityInfo = ActivityRegistry::getByCode($activityCode);
            
            $registerResult = $bitrixClient->call('bizproc.activity.add', [
                'CODE' => $activityCode,
                'NAME' => $activityInfo['name'],
                'DESCRIPTION' => $activityInfo['description'],
                // URL обработчика - единый роутер с параметром ACTIVITY_CODE
                'HANDLER_URL' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/activity/handle?code=' . $activityCode,
            ]);

            if (empty($registerResult['result'])) {
                throw new Exception('Не удалось зарегистрировать активность в Bitrix24: ' . 
                    json_encode($registerResult));
            }

            // Обновляем статус
            $stateManager->setStatus($activityCode, [
                'registered' => true,
                'enabled' => true,
            ]);
        } else {
            // Активность уже зарегистрирована, просто включаем
            $stateManager->enable($activityCode);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Активность успешно подключена',
            'status' => $stateManager->getStatus($activityCode),
        ]);

    } elseif ($action === 'disable') {
        // Отключаем активность
        $stateManager->disable($activityCode);

        echo json_encode([
            'success' => true,
            'message' => 'Активность отключена',
            'status' => $stateManager->getStatus($activityCode),
        ]);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Недопустимое действие. Используйте enable или disable']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
