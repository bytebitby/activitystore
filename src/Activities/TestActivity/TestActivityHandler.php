<?php

namespace App\Activities\TestActivity;

class TestActivityHandler
{
    public function execute(array $params): array
    {
        file_put_contents(
            __DIR__ . '/../../../storage/test_activity.log',
            print_r([
                'time' => date('Y-m-d H:i:s'),
                'params' => $params
            ], true),
            FILE_APPEND
        );

        return [
            'success' => true,
            'message' => 'Test activity executed',
            'returnValues' => [
                'executed_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
}