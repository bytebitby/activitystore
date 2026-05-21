<?php

namespace App\Activities\TestActivity;

/**
 * Обработчик тестовой активности.
 * Пример простой активности для проверки работы витрины.
 */
class TestActivityHandler
{
    /**
     * Выполнение активности
     * @param array $params Параметры от Bitrix24
     * @return array Результат выполнения
     */
    public function execute(array $params): array
    {
        // Логика активности
        // Здесь будет код, который выполняется при вызове активности в бизнес-процессе
        
        return [
            'success' => true,
            'message' => 'Тестовая активность выполнена успешно!',
            'data' => [
                'timestamp' => time(),
                'params' => $params,
            ],
        ];
    }
}
