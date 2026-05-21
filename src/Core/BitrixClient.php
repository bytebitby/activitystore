<?php
namespace App\Core;

// Заглушка для MVP. В будущем здесь будет логика работы с REST API Битрикс24
class BitrixClient
{
    public function __construct($webhookUrl = null)
    {
        // Инициализация
    }

    public function call($method, $params = [])
    {
        // Возвращаем пустой результат для теста
        return ['result' => 'ok'];
    }
}