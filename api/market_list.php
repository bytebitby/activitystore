<?php

/**
 * API эндпоинт для получения списка активностей и их статусов.
 * Используется витриной для отображения доступных активностей.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRegistry;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

header('Content-Type: application/json');

try {
    // Инициализация клиента Bitrix24 и менеджера состояния
    $bitrixClient = new BitrixClient();
    $stateManager = new ActivityStateManager($bitrixClient);
    
    // Получение всех активностей из реестра
    $allActivities = ActivityRegistry::getAll();
    
    // Формирование ответа со статусами
    $result = [];
    foreach ($allActivities as $code => $activityInfo) {
        $status = $stateManager->getStatus($code);
        
        $result[] = [
            'code' => $code,
            'name' => $activityInfo['name'],
            'description' => $activityInfo['description'],
            'icon' => $activityInfo['icon'] ?? null,
            'registered' => $status['registered'] ?? false,
            'enabled' => $status['enabled'] ?? false,
        ];
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $result,
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка: ' . $e->getMessage(),
    ]);
}
