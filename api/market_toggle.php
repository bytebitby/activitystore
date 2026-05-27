<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\ActivityRegistry;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$code = $input['activity_code'] ?? null;

if (!$code) {
    http_response_code(400);
    echo json_encode(['error' => 'activity_code required']);
    exit;
}

$activity = ActivityRegistry::getByCode($code);

if (!$activity) {
    http_response_code(404);
    echo json_encode(['error' => 'Activity not found']);
    exit;
}

/* PORTAL */
$portalsFile = __DIR__ . '/../storage/portals.json';
$portals = json_decode(file_get_contents($portalsFile), true);

$portalId = array_key_first($portals);
$portal = $portals[$input['member_id']] ?? array_values($portals)[0];

$domain = $portal['domain'];
$auth = $portal['auth_id'];

/* REGISTER BITRIX ACTIVITY */
$url = "https://{$domain}/rest/bizproc.activity.add.json";

$postFields = [
    'auth' => $auth,
    'CODE' => $activity['code'],
    'NAME' => $activity['bizproc_name'] ?? $activity['name'],
    'DESCRIPTION' => $activity['description'],
    'HANDLER' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/activity_handle.php?code=' . $activity['code']
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

/* SAVE STATE */
$stateFile = __DIR__ . '/../storage/activity_states.json';

$state = file_exists($stateFile)
    ? json_decode(file_get_contents($stateFile), true)
    : [];

$state[$portalId][$code] = [
    'registered' => true,
    'enabled' => true,
    'bitrix_result' => $result
];

file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'activity' => $code,
    'bitrix' => $result
]);