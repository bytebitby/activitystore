<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityLifecycle;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

$storageDir = __DIR__ . '/../storage';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

/**
 * BITRIX INSTALL PAYLOAD
 */
$data = $_REQUEST;

$memberId = $data['member_id'] ?? null;
$domain = $data['DOMAIN'] ?? null;
$authId = $data['AUTH_ID'] ?? null;

if (!$memberId || !$domain || !$authId) {
    http_response_code(400);

    echo json_encode([
        'error' => 'Missing required install data',
        'received' => $data
    ]);

    exit;
}

/**
 * SAVE PORTAL
 */
$portalsFile = $storageDir . '/portals.json';

$portals = [];

if (file_exists($portalsFile)) {
    $portals = json_decode(file_get_contents($portalsFile), true) ?? [];
}

$portals[$memberId] = [
    'domain' => $domain,
    'auth_id' => $authId,
    'member_id' => $memberId,
    'installed_at' => date('Y-m-d H:i:s')
];

file_put_contents($portalsFile, json_encode($portals, JSON_PRETTY_PRINT));

/**
 * INIT LIFECYCLE
 */
$stateManager = new ActivityStateManager();

require_once __DIR__ . '/../src/Core/ActivityRegistry.php';
$activities = \App\Core\ActivityRegistry::getAll();

$bitrix = new BitrixClient($domain, $authId);

/**
 * SYNC + RECONCILE
 */
$stateManager->syncPortal($memberId, $activities);
$stateManager->reconcile($memberId, $domain, $authId, $bitrix);

/**
 * LIFECYCLE (если он реально нужен сейчас)
 */
$lifecycle = new ActivityLifecycle($stateManager, $bitrix);

$lifecycleResult = $lifecycle->install($memberId, $activities);

/**
 * INSTALL ACTIVITIES (from registry)
 */
require_once __DIR__ . '/../src/Core/ActivityRegistry.php';

$activities = \App\Core\ActivityRegistry::getAll();

$lifecycleResult = $lifecycle->install($memberId, $activities);

// AUTO SYNC (важно для reinstall)
$stateManager->syncPortal($memberId, $activities);

/**
 * RESPONSE
 */
echo json_encode([
    'status' => 'success',
    'message' => 'Portal installed successfully',
    'portal' => [
        'member_id' => $memberId,
        'domain' => $domain
    ],
    'lifecycle' => $lifecycleResult
]);