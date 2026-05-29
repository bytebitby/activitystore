<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRegistry;
use App\Core\ActivityStateManager;
use App\Core\BitrixClient;

/**
 * ONLY POST
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'error' => 'POST only'
    ]);

    exit;
}

/**
 * INPUT
 */

$input = json_decode(
    file_get_contents('php://input'),
    true
);

$activityCode = $input['activity_code'] ?? null;
$action = $input['action'] ?? 'enable';

if (!$activityCode) {

    http_response_code(400);

    echo json_encode([
        'error' => 'activity_code required'
    ]);

    exit;
}

/**
 * REGISTRY
 */

$activity = ActivityRegistry::getByCode($activityCode);

if (!$activity) {

    http_response_code(404);

    echo json_encode([
        'error' => 'Activity not found'
    ]);

    exit;
}

/**
 * PORTALS
 */

$portalsFile = __DIR__ . '/../storage/portals.json';

$portals = json_decode(
    file_get_contents($portalsFile),
    true
);

if (!$portals || !is_array($portals)) {

    http_response_code(500);

    echo json_encode([
        'error' => 'No portals installed'
    ]);

    exit;
}

$portalId = array_key_first($portals);

$portal = $portals[$portalId];

$domain = $portal['domain'];
$authId = $portal['auth_id'];

/**
 * SERVICES
 */

$bitrix = new BitrixClient(
    $domain,
    $authId
);

$stateManager = new ActivityStateManager();

/**
 * CURRENT STATE
 */

$currentState = $stateManager->getStatus(
    $portalId,
    $activityCode
);

/**
 * DISABLE
 */

if ($action === 'disable') {

    $bitrix->call(
        'bizproc.activity.delete',
        [
            'CODE' => strtoupper($activityCode)
        ]
    );

    $stateManager->uninstall(
        $portalId,
        $activityCode
    );

    echo json_encode([
        'success' => true,
        'message' => 'Activity uninstalled'
    ]);

    exit;
}

/**
 * ALREADY INSTALLED
 */

if ($currentState['registered'] ?? false) {

    echo json_encode([
        'success' => true,
        'message' => 'Already installed',
        'state' => $currentState
    ]);

    exit;
}

/**
 * REGISTER
 */

$result = $bitrix->call(
    'bizproc.activity.add',
    [
        'CODE' => strtoupper($activityCode),

        'NAME' => $activity['name'],

        'DESCRIPTION' => $activity['description'],

        'HANDLER' =>
            'https://' .
            $_SERVER['HTTP_HOST'] .
            '/api/activity_handle.php',

        'USE_SUBSCRIPTION' => 'Y'
    ]
);

/**
 * SAVE STATE
 */

$stateManager->install(
    $portalId,
    $activityCode
);

/**
 * RESPONSE
 */

echo json_encode([
    'success' => true,
    'message' => 'Activity installed',
    'bitrix' => $result
]);