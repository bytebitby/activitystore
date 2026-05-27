<?php

file_put_contents(__DIR__ . '/../storage/install_dump.txt', print_r($_REQUEST, true));

header('Content-Type: application/json; charset=utf-8');

$storageDir = __DIR__ . '/../storage';
$portalsFile = $storageDir . '/portals.json';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

$data = $_REQUEST;

/**
 * Bitrix install payload
 */
$domain = $data['DOMAIN'] ?? '';
$authId = $data['AUTH_ID'] ?? '';
$refreshId = $data['REFRESH_ID'] ?? '';
$memberId = $data['member_id'] ?? '';
$applicationToken = $data['APPLICATION_TOKEN'] ?? '';

if (!$domain || !$authId || !$memberId) {
    http_response_code(400);

    echo json_encode([
        'error' => 'Missing required install data',
        'received' => $data
    ]);

    exit;
}

/**
 * Load existing portals
 */
$portals = [];

if (file_exists($portalsFile)) {
    $existing = json_decode(file_get_contents($portalsFile), true);

    if (is_array($existing)) {
        $portals = $existing;
    }
}

/**
 * Normalize portal structure
 */
$portalData = [
    'domain' => $domain,
    'auth_id' => $authId,
    'refresh_id' => $refreshId,
    'member_id' => $memberId,
    'application_token' => $applicationToken,
    'installed_at' => date('Y-m-d H:i:s')
];

/**
 * IMPORTANT:
 * member_id = PRIMARY KEY
 * overwrite / update portal safely
 */
$portals[$memberId] = $portalData;

/**
 * Save
 */
file_put_contents(
    $portalsFile,
    json_encode($portals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/**
 * Response
 */
echo json_encode([
    'status' => 'success',
    'message' => 'Portal installed successfully',
    'portal' => [
        'domain' => $domain,
        'member_id' => $memberId
    ]
]);