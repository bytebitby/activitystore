<?php

require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/portal_storage.php';
require __DIR__ . '/../src/bitrix.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/api/enable-test')
{
    $activities = require __DIR__ . '/../storage/activities.php';

    $activity = $activities[0];

    $portals = portals_all();

    $portal = $portals[0];

    $result = bitrix_call(
        $portal,
        'bizproc.activity.add',
        [
            'CODE' => $activity['CODE'],
            'HANDLER' => $activity['HANDLER'],
            'USE_SUBSCRIPTION' => $activity['USE_SUBSCRIPTION'],
            'NAME' => $activity['NAME'],
            'DESCRIPTION' => $activity['DESCRIPTION'],
            'PROPERTIES' => $activity['PROPERTIES'],
            'RETURN_PROPERTIES' => $activity['RETURN_PROPERTIES'],
        ]
    );

    json_response($result);
}

if ($uri === '/api/rest-test')
{
    $portals = portals_all();

    if (empty($portals))
    {
        json_response([
            'ok' => false,
            'error' => 'No portals'
        ], 400);
    }

    $portal = $portals[0];

    $result = bitrix_call(
        $portal,
        'app.info'
    );

    json_response($result);
}

if ($uri === '/install')
{
    $portal = [
        'domain' => $_REQUEST['DOMAIN'] ?? '',
        'member_id' => $_REQUEST['member_id'] ?? '',
        'access_token' => $_REQUEST['AUTH_ID'] ?? '',
        'refresh_token' => $_REQUEST['REFRESH_ID'] ?? '',
        'server_endpoint' => $_REQUEST['SERVER_ENDPOINT'] ?? '',
        'application_token' => $_REQUEST['APPLICATION_TOKEN'] ?? '',
        'installed_at' => time()
    ];

    log_message('INSTALL: ' . json_encode($portal));

    portal_add($portal);

    echo '
    <h1>Bitrix Activity MVP</h1>
    <p>Приложение установлено</p>
    ';

    exit;
}

if ($uri === '/api/health')
{
    json_response([
        'ok' => true,
        'time' => time(),
        'php' => PHP_VERSION
    ]);
}

if ($uri === '/api/test-log')
{
    log_message('Test log entry');

    json_response([
        'ok' => true,
        'logged' => true
    ]);
}

if ($uri === '/api/test-save')
{
    portal_add([
        'domain' => 'test.bitrix24.ru',
        'member_id' => uniqid(),
        'created_at' => time()
    ]);

    json_response([
        'ok' => true
    ]);
}

if ($uri === '/api/methods')
{
    $portals = portals_all();

    $portal = $portals[0];

    $result = bitrix_call(
        $portal,
        'methods'
    );

    json_response($result);
}

json_response([
    'ok' => true,
    'app' => 'Bitrix Activity MVP'
]);