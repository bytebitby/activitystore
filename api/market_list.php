<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRegistry;
use App\Core\ActivityStateManager;

/**
 * PORTALS
 */
$portalsFile = __DIR__ . '/../storage/portals.json';

if (!file_exists($portalsFile)) {
    echo json_encode([
        'success' => false,
        'error' => 'portals.json not found'
    ]);

    exit;
}

$portals = json_decode(
    file_get_contents($portalsFile),
    true
);

$portalId = array_key_first($portals);

if (!$portalId) {
    echo json_encode([
        'success' => false,
        'error' => 'No portal installed'
    ]);

    exit;
}

/**
 * STATE
 */
$stateManager = new ActivityStateManager();

/**
 * ACTIVITIES
 */
$activities = ActivityRegistry::getAll();

$result = [];

foreach ($activities as $code => $activity) {

    $status = $stateManager->getStatus(
        $portalId,
        $code
    );

    $result[] = [
        'code' => $code,
        'name' => $activity['name'],
        'description' => $activity['description'],
        'registered' => $status['registered'] ?? false,
        'enabled' => $status['enabled'] ?? false
    ];
}

/**
 * RESPONSE
 */
echo json_encode([
    'success' => true,
    'portal_id' => $portalId,
    'activities' => $result
]);