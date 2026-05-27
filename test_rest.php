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

$url = "https://{$domain}/rest/app.info.json?auth={$auth}";

echo "<h2>REST TEST</h2>";
echo "<p><b>URL:</b> {$url}</p>";

$response = file_get_contents($url);

echo "<h3>Response:</h3>";
echo "<pre>";

print_r(json_decode($response, true));

echo "</pre>";