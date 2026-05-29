<?php

namespace App\Core;

class BitrixClient
{
    private string $domain;
    private string $authId;

    public function __construct(string $domain, string $authId)
    {
        $this->domain = $domain;
        $this->authId = $authId;
    }

    public function call(string $method, array $params = []): array
    {
        $url = "https://{$this->domain}/rest/{$method}.json?auth={$this->authId}";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (!$decoded) {
            throw new \Exception('Invalid Bitrix response');
        }

        return $decoded;
    }
        
    public function getRegisteredActivities(string $domain, string $authId): array
    {
        $url = "https://{$domain}/rest/bizproc.activity.list.json?auth={$authId}";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $decoded = json_decode($response, true);

        return $decoded['result'] ?? [];
    }
}