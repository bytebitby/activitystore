<?php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRouter;
use App\Core\ActivityStateManager;

/**
 * DEBUG LOG (оставляем, полезно)
 */
file_put_contents(
    __DIR__ . '/../storage/activity_debug.txt',
    print_r([
        'time' => date('Y-m-d H:i:s'),
        '_REQUEST' => $_REQUEST,
        'RAW' => file_get_contents('php://input')
    ], true) . PHP_EOL,
    FILE_APPEND
);

/**
 * INPUT
 */

$activityCode = $_REQUEST['code'] ?? null;

if (!$activityCode) {

    echo json_encode([
        'success' => false,
        'error' => 'No activity code'
    ]);

    exit;
}

/**
 * PORTAL ID
 */

$memberId = $_REQUEST['auth']['member_id'] ?? null;

if (!$memberId) {

    echo json_encode([
        'success' => false,
        'error' => 'No member_id'
    ]);

    exit;
}

/**
 * STATE + ROUTER
 */

$stateManager = new ActivityStateManager();

$router = new ActivityRouter($stateManager);

/**
 * CLEAN PARAMS
 */

$params = $_REQUEST;

unset($params['code']);

/**
 * EXECUTE
 */

$result = $router->handle(
    $memberId,
    strtolower($activityCode),
    $params
);

/**
 * RESPONSE (Bitrix expects result wrapper)
 */

echo json_encode([
    'result' => $result
]);