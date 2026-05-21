<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/portal_storage.php';

function bitrix_call(array $portal, string $method, array $fields = []): array
{
    $url = rtrim($portal['server_endpoint'], '/')
        . '/'
        . $method;

    $fields['auth'] = $portal['access_token'];

    log_message('BITRIX CALL: ' . $method);

    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POSTFIELDS => http_build_query($fields),

      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);

    $error = curl_error($ch);

    curl_close($ch);

    if ($error)
    {
        return [
            'error' => $error
        ];
    }

    return json_decode($response, true) ?: [
        'raw' => $response
    ];
}