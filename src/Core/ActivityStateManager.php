<?php

namespace App\Core;

class ActivityStateManager
{
    private BitrixClient $bitrixClient;
    private string $optionName = 'activity_statuses';

    public function __construct(BitrixClient $bitrixClient)
    {
        $this->bitrixClient = $bitrixClient;
    }

    /**
     * Получает все статусы активностей из хранилища
     */
    private function getOptions(): array
    {
        // В MVP используем заглушку, так как реальный запрос к Б24 требует вебхук
        // В будущем здесь будет: $result = $this->bitrixClient->call('app.option.get', ['name' => $this->optionName]);
        // return $result['result'] ?? [];
        
        return []; 
    }

    /**
     * Сохраняет все статусы
     */
    private function setOptions(array $options): bool
    {
        // Заглушка для сохранения
        // $this->bitrixClient->call('app.option.set', ['name' => $this->optionName, 'value' => $options]);
        return true;
    }

    /**
     * Возвращает статус конкретной активности
     * Всегда возвращает массив: ['registered' => bool, 'enabled' => bool]
     */
    public function getStatus(string $activityCode): array
    {
        $allOptions = $this->getOptions();

        if (isset($allOptions[$activityCode]) && is_array($allOptions[$activityCode])) {
            return $allOptions[$activityCode];
        }

        // Статус по умолчанию (не зарегистрирована, не включена)
        return [
            'registered' => false,
            'enabled' => false
        ];
    }

    /**
     * Устанавливает статус активности
     */
    public function setStatus(string $activityCode, bool $registered, bool $enabled): bool
    {
        $allOptions = $this->getOptions();

        $allOptions[$activityCode] = [
            'registered' => $registered,
            'enabled' => $enabled
        ];

        return $this->setOptions($allOptions);
    }
}