<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityLifecycle;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

$storageDir = __DIR__ . '/../storage';
$portalsFile = $storageDir . '/portals.json';

/**
 * INPUT
 */
$data = $_REQUEST;

$memberId = $data['member_id'] ?? null;

if (!$memberId) {
    http_response_code(400);

    echo json_encode([
        'error' => 'member_id required'
    ]);

    exit;
}

/**
 * LOAD PORTAL
 */
$portals = [];

if (file_exists($portalsFile)) {
    $portals = json_decode(file_get_contents($portalsFile), true) ?? [];
}

if (!isset($portals[$memberId])) {
    echo json_encode([
        'success' => true,
        'message' => 'Portal already removed'
    ]);
    exit;
}

/**
 * REMOVE PORTAL FROM STORAGE
 */
unset($portals[$memberId]);

file_put_contents(
    $portalsFile,
    json_encode($portals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/**
 * CLEAN STATE
 */
$stateManager = new ActivityStateManager();
$bitrix = new BitrixClient();

$lifecycle = new ActivityLifecycle($stateManager, $bitrix);

$lifecycle->uninstall($memberId);

/**
 * RESPONSE
 */
echo json_encode([
    'success' => true,
    'message' => 'Portal uninstalled successfully',
    'member_id' => $memberId
]);