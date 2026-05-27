<?php

header('Content-Type: text/html; charset=utf-8');

$portalsFile = __DIR__ . '/storage/portals.json';

if (!file_exists($portalsFile)) {
    exit('portals.json not found');
}

$portals = json_decode(file_get_contents($portalsFile), true);

if (!$portals || empty($portals[0])) {
    exit('No portal data');
}

$portal = $portals[0];

$domain = $portal['domain'];
$auth = $portal['auth_id'];

$activityCode = 'TEST_ACTIVITY_' . time();

$handlerUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/api/activity_handle.php';

$url = "https://{$domain}/rest/bizproc.activity.add.json?auth={$auth}";

$fields = [
    'CODE' => $activityCode,
    'HANDLER' => $handlerUrl,
    'NAME' => 'Test Activity MVP',
    'DESCRIPTION' => 'Test activity from MVP',
    'PROPERTIES' => [],
    'RETURN_PROPERTIES' => []
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

$error = curl_error($ch);

curl_close($ch);

echo "<h2>REGISTER ACTIVITY TEST</h2>";

echo "<p><b>URL:</b> {$url}</p>";

if ($error) {
    echo "<h3>CURL ERROR</h3>";
    echo "<pre>{$error}</pre>";
    exit;
}

$data = json_decode($response, true);

echo "<h3>Response:</h3>";

echo "<pre>";
print_r($data);
echo "</pre>";

if (isset($data['result'])) {
    echo "<h2 style='color:green'>SUCCESS</h2>";
    echo "<p>Activity code: <b>{$activityCode}</b></p>";
} else {
    echo "<h2 style='color:red'>FAILED</h2>";
}