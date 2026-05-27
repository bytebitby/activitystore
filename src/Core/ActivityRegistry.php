<?php

namespace App\Core;

/**
 * Реестр доступных активностей.
 * Хранит метаданные всех возможных активностей приложения.
 */
class ActivityRegistry
{
    private static array $activities = [
        'test_activity' => [
            'code' => 'test_activity',
            'name' => 'Тестовая активность',
            'description' => 'Пример активности для проверки работы витрины',
            'icon' => '/icons/test_activity.png',
            'handler' => \App\Activities\TestActivity\TestActivityHandler::class,
        ],
        // Сюда будут добавляться новые активности
    ];

    /**
     * Получить список всех доступных активностей
     */
    public static function getAll(): array
    {
        return self::$activities;
    }

    /**
     * Получить активность по коду
     */
    public static function getByCode(string $code): ?array
    {
        return self::$activities[$code] ?? null;
    }

    /**
     * Проверить существование активности
     */
    public static function exists(string $code): bool
    {
        return isset(self::$activities[$code]);
    }
}
