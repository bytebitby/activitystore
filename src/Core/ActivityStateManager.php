<?php

namespace App\Core;

class ActivityStateManager
{
    private string $file;

    public function __construct()
    {
        $this->file = __DIR__ . '/../../storage/activity_states.json';

        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode(new \stdClass(), JSON_PRETTY_PRINT));
        }
    }

    private function read(): array
    {
        $data = json_decode(file_get_contents($this->file), true);

        return is_array($data) ? $data : [];
    }

    private function write(array $data): void
    {
        file_put_contents(
            $this->file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * GET STATUS
     */
    public function getStatus(string $memberId, string $activityCode): array
    {
        $data = $this->read();

        return $data[$memberId]['activities'][$activityCode] ?? [
            'registered' => false,
            'enabled' => false
        ];
    }

    /**
     * SET STATUS
     */
    public function setStatus(
        string $memberId,
        string $activityCode,
        bool $registered,
        bool $enabled,
        array $meta = []
    ): void {
        $data = $this->read();

        if (!isset($data[$memberId])) {
            $data[$memberId] = [
                'activities' => []
            ];
        }

        $data[$memberId]['activities'][$activityCode] = [
            'registered' => $registered,
            'enabled' => $enabled,
            'bitrix_meta' => $meta,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->write($data);
    }

    /**
     * DISABLE
     */
    public function disable(string $memberId, string $activityCode): void
    {
        $data = $this->read();

        if (isset($data[$memberId]['activities'][$activityCode])) {
            $data[$memberId]['activities'][$activityCode]['enabled'] = false;
            $data[$memberId]['activities'][$activityCode]['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->write($data);
    }

    /**
     * CHECK ACTIVE
     */
    public function isActive(string $memberId, string $activityCode): bool
    {
        return (bool)(
            $this->getStatus($memberId, $activityCode)['enabled'] ?? false
        );
    }

    public function removePortal(string $memberId): void
    {
        $data = $this->read();

        if (isset($data[$memberId])) {
            unset($data[$memberId]);
        }

        $this->write($data);
    }

    public function syncPortal(
        string $memberId,
        array $activities,
        \App\Core\BitrixClient $bitrix = null,
        string $domain = null
    ): void
    {
        $data = $this->read();

        if (!isset($data[$memberId])) {
           $data[$memberId] = [
                'activities' => []
            ];
        }

        foreach ($activities as $code => $activity) {

            if (!isset($data[$memberId]['activities'][$code])) {
                $data[$memberId]['activities'][$code] = [
                    'registered' => false,
                    'enabled' => false,
                    'bitrix_meta' => [],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_via' => 'auto_sync'
                ];
            }

            if (
                $bitrix &&
                $domain &&
                ($data[$memberId]['activities'][$code]['enabled'] ?? false) &&
                !($data[$memberId]['activities'][$code]['registered'] ?? false)
            ) {
                $bitrix->call($domain, 'bizproc.activity.add', [
                    'CODE' => strtoupper($code),
                    'NAME' => $activity['name'] ?? $code,
                    'DESCRIPTION' => $activity['description'] ?? '',
                    'HANDLER' => $_SERVER['HTTP_HOST'] . '/api/activity_handle.php'
                ]);

                $data[$memberId]['activities'][$code]['registered'] = true;
            }
        }

        $this->write($data);
    }

    public function reconcile(
        string $memberId,
        string $domain,
        string $authId,
        \App\Core\BitrixClient $bitrix
    ): void {

        $data = $this->read();

        if (!isset($data[$memberId]['activities'])) {
            return;
        }

    /**
     * 1. Bitrix state
     */
        $bitrixActivities = $bitrix->getRegisteredActivities($domain, $authId);

        $bitrixMap = [];
        foreach ($bitrixActivities as $a) {
            $bitrixMap[strtolower($a['CODE'])] = true;
        }

    /**
     * 2. Local sync check
     */
        foreach ($data[$memberId]['activities'] as $code => &$state) {

            $codeLower = strtolower($code);

            $inBitrix = isset($bitrixMap[$codeLower]);

        /**
         * CASE 1: local says registered but Bitrix missing
         */
            if ($state['registered'] && !$inBitrix) {

            // re-register
                $bitrix->call('bizproc.activity.add', [
                    'CODE' => strtoupper($code),
                    'NAME' => $code,
                    'DESCRIPTION' => '',
                    'HANDLER' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/activity_handle.php'
                ]);

                $state['registered'] = true;
                $state['bitrix_meta']['recovered'] = true;
                $state['bitrix_meta']['recovered_at'] = date('Y-m-d H:i:s');
            }

        /**
         * CASE 2: Bitrix exists but local doesn't know
         */
            if (!$state['registered'] && $inBitrix) {
                $state['registered'] = true;
                $state['bitrix_meta']['imported'] = true;
            }

        /**
         * CASE 3: enable consistency
         */
            if ($state['enabled'] && !$state['registered']) {
                $state['enabled'] = false;
            }
        }

        $this->write($data);
    }
}