<?php

namespace App\Core;

/**
 * Динамический роутер для обработки вызовов активностей.
 * Принимает ACTIVITY_CODE от Bitrix24 и направляет запрос нужному обработчику.
 */
class ActivityRouter
{
    private ActivityStateManager $stateManager;

    public function __construct(ActivityStateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * Обработать вызов активности
     * @param string $activityCode Код активности (передается из Bitrix24)
     * @param array $params Параметры вызова от Bitrix24
     * @return array Результат выполнения
     */
    public function handle(string $activityCode, array $params = []): array
    {
        // Проверка существования активности в реестре
        if (!ActivityRegistry::exists($activityCode)) {
            return [
                'success' => false,
                'error' => "Активность '{$activityCode}' не найдена в реестре",
            ];
        }

        // Проверка статуса активности
        if (!$this->stateManager->isActive($activityCode)) {
            return [
                'success' => false,
                'error' => "Активность '{$activityCode}' не активирована. Пожалуйста, подключите её в витрине.",
            ];
        }

        // Получение информации об активности
        $activityInfo = ActivityRegistry::getByCode($activityCode);
        $handlerClass = $activityInfo['handler'];

        // Проверка существования класса-обработчика
        if (!class_exists($handlerClass)) {
            return [
                'success' => false,
                'error' => "Обработчик для активности '{$activityCode}' не найден",
            ];
        }

        // Создание экземпляра обработчика и выполнение
        try {
            $handler = new $handlerClass();
            return $handler->execute($params);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Ошибка выполнения активности: " . $e->getMessage(),
            ];
        }
    }
}
