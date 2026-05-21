<?php

namespace App\Core;

/**
 * Менеджер состояния активностей.
 * Управляет статусами registered и enabled для каждого портала.
 * Использует app.option Bitrix24 для хранения данных.
 */
class ActivityStateManager
{
    private BitrixClient $bitrixClient;

    public function __construct(BitrixClient $bitrixClient)
    {
        $this->bitrixClient = $bitrixClient;
    }

    /**
     * Получить статус активности для текущего портала
     * @return array ['registered' => bool, 'enabled' => bool]
     */
    public function getStatus(string $activityCode): array
    {
        $optionKey = 'activity_status_' . $activityCode;
        $result = $this->bitrixClient->call('app.option.get', ['NAME' => $optionKey]);
        
        if (!empty($result['result'])) {
            return $result['result'];
        }
        
        // По умолчанию активность не зарегистрирована и не включена
        return [
            'registered' => false,
            'enabled' => false,
        ];
    }

    /**
     * Установить статус активности
     */
    public function setStatus(string $activityCode, array $status): bool
    {
        $optionKey = 'activity_status_' . $activityCode;
        $result = $this->bitrixClient->call('app.option.set', [
            'NAME' => $optionKey,
            'VALUE' => $status,
        ]);
        
        return !empty($result['result']);
    }

    /**
     * Проверить, активна ли активность (зарегистрирована и включена)
     */
    public function isActive(string $activityCode): bool
    {
        $status = $this->getStatus($activityCode);
        return ($status['registered'] ?? false) && ($status['enabled'] ?? false);
    }

    /**
     * Активировать активность
     */
    public function enable(string $activityCode): bool
    {
        $status = $this->getStatus($activityCode);
        $status['enabled'] = true;
        return $this->setStatus($activityCode, $status);
    }

    /**
     * Деактивировать активность
     */
    public function disable(string $activityCode): bool
    {
        $status = $this->getStatus($activityCode);
        $status['enabled'] = false;
        return $this->setStatus($activityCode, $status);
    }
}
