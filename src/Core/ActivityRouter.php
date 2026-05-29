<?php

namespace App\Core;

class ActivityRouter
{
    private ActivityStateManager $stateManager;

    public function __construct(ActivityStateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(
        string $memberId,
        string $activityCode,
        array $params = []
    ): array {

        $activityCode = strtolower($activityCode);

        /**
         * REGISTRY CHECK
         */
        if (!ActivityRegistry::exists($activityCode)) {
            return [
                'success' => false,
                'error' => 'Activity not found in registry'
            ];
        }

        /**
         * STATE CHECK
         */
        $status = $this->stateManager->getStatus(
            $memberId,
            $activityCode
        );

        if (!($status['enabled'] ?? false)) {
            return [
                'success' => false,
                'error' => 'Activity disabled'
            ];
        }

        /**
         * HANDLER RESOLVE
         */
        $activity = ActivityRegistry::getByCode($activityCode);

        $handlerClass = $activity['handler'];

        if (!class_exists($handlerClass)) {
            return [
                'success' => false,
                'error' => 'Handler class not found'
            ];
        }

        /**
         * EXECUTE
         */
        try {
            $handler = new $handlerClass();
            return $handler->execute($params);

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}