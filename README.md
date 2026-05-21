# Bitrix24 Activity Marketplace (MVP)

Приложение-витрина для управления кастомными активностями бизнес-процессов в Bitrix24. Позволяет пользователю выбирать и подключать нужные активности из общего пула после установки приложения.

## 🚀 Статус
**MVP (Minimum Viable Product)**
- ✅ Реестр активностей (Activity Registry)
- ✅ Менеджер состояний (StateManager) через `app.option`
- ✅ Динамический роутер для обработки вызовов
- ✅ API для получения списка и переключения статусов
- ✅ Базовый фронтенд (Витрина)
- ⚠️ В разработке: Полная интеграция с `bizproc.activity.add` (требуется доработка обработки JSON)

## 🏗 Архитектура

### Ядро (`src/Core`)
- `ActivityRegistry.php`: Хранит метаданные всех доступных активностей (код, название, описание).
- `ActivityStateManager.php`: Управляет статусами (`registered`, `enabled`) для каждого портала через `app.option`.
- `BitrixClient.php`: Обертка для HTTP-запросов к API Bitrix24.
- `ActivityRouter.php`: Единая точка входа для вызовов от БП, перенаправляющая запросы конкретным обработчикам.

### Активности (`src/Activities`)
Папка с реализацией конкретных активностей. Каждая активность имеет свой класс-обработчик.
- `TestActivity`: Тестовая активность для отладки.

### API (`api`)
- `market_list.php`: Возвращает список активностей со статусами для текущего портала.
- `market_toggle.php`: Обрабатывает запросы на включение/выключение активности.
- `activity_handle.php`: Принимает входящие вебхуки от Bitrix24 при запуске активности в БП.

## 🛠 Установка и Запуск (Локально)

1. **Требования**: PHP 8.0+, Composer, Ngrok.
2. **Установка зависимостей**:
   ```bash
   composer install

Запуск сервера: php -S localhost:8000
Запуск туннеля: ngrok http 8000

Доступ:
Витрина: https://<твой-ngrok-url>/marketplace.html
API Список: https://<твой-ngrok-url>/api/market_list.php

Структура: 
├── api/                  # Эндпоинты API
│   ├── market_list.php
│   ├── market_toggle.php
│   └── activity_handle.php
├── config/               # Конфигурация
│   └── bootstrap.php
├── src/
│   ├── Core/             # Ядро системы
│   │   ├── ActivityRegistry.php
│   │   ├── ActivityStateManager.php
│   │   ├── ActivityRouter.php
│   │   └── BitrixClient.php
│   └── Activities/       # Реализации активностей
│       └── TestActivity/
├── marketplace.html      # Фронтенд витрины
├── composer.json
└── README.md

🔜 Следующие шаги
Исправить обработку JSON в market_toggle.php (убрать лишний вывод).
Реализовать реальный вызов bizproc.activity.add в методе включения.
Добавить логику проверки статуса enabled внутри обработчиков активностей.
Расширить пул тестовых активностей.