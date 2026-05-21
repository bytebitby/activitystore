<?php

function portals_all(): array
{
    $path = __DIR__ . '/../storage/portals.json';

    if (!file_exists($path))
    {
        return [];
    }

    $json = file_get_contents($path);

    return json_decode($json, true) ?: [];
}

function portals_save(array $items): void
{
    $path = __DIR__ . '/../storage/portals.json';

    file_put_contents(
        $path,
        json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function portal_add(array $portal): void
{
    $items = portals_all();

    $items[] = $portal;

    portals_save($items);
}