<?php

namespace App\Core;

/**
 * Централизованный lifecycle активностей:
 * install / uninstall / sync
 */
class ActivityLifecycle
{
    private ActivityStateManager $stateManager;
    private BitrixClient $bitrix;

    public function __construct(
        ActivityStateManager $stateManager,
        BitrixClient $bitrix
    ) {
        $this->stateManager = $stateManager;
        $this->bitrix = $bitrix;
    }

    /**
     * INSTALL activity for portal
     */
    public function install(string $memberId, array $activities): array
    {
        $result = [];

        foreach ($activities as $activityCode => $activity) {

            $this->stateManager->setStatus(
                $memberId,
                $activityCode,
                false,
                false,
                [
                    'installed_via' => 'lifecycle',
                    'installed_at' => date('Y-m-d H:i:s')
                ]
            );

            $result[] = $activityCode;
        }

        return [
            'success' => true,
            'installed' => $result
        ];
    }

    /**
     * UNINSTALL portal cleanup
     */
    public function uninstall(string $memberId): array
    {
        $this->stateManager->removePortal($memberId);

        return [
            'success' => true,
            'message' => 'Portal lifecycle removed'
        ];
    }

    /**
     * SYNC (Bitrix recovery / reinstall safety)
     */
    public function sync(string $memberId, array $activities): array
    {
        foreach ($activities as $activityCode => $activity) {

            $status = $this->stateManager->getStatus($memberId, $activityCode);

            if (!isset($status['registered'])) {
                $this->stateManager->setStatus(
                    $memberId,
                    $activityCode,
                    false,
                    false,
                    [
                        'synced_at' => date('Y-m-d H:i:s')
                    ]
                );
            }
        }

        return [
            'success' => true,
            'message' => 'Sync completed'
        ];
    }
}