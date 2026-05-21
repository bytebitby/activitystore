<?php

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);

    header('Content-Type: application/json');

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function log_message(string $message): void
{
    $line = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;

    file_put_contents(
        __DIR__ . '/../storage/log.txt',
        $line,
        FILE_APPEND
    );
}