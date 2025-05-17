<?php
// Конфигурация
define('BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN'));
define('ADMIN_ID', getenv('ADMIN_TELEGRAM_ID'));
define('CHANNEL_ID', getenv('REPORT_CHANNEL_ID')); // Формат: -1001234567890
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('USERS_FILE', 'users.json');
define('REPORT_INTERVAL', 600); // 10 минут в секундах

// Инициализация
if (!file_exists('last_report.txt')) file_put_contents('last_report.txt', 0);

// Функция отправки отчёта
function sendActivityReport() {
    $users = json_decode(file_get_contents(USERS_FILE), true);
    $activeUsers = count(array_filter($users['users'] ?? [], 
        fn($u) => time() - ($u['last_activity'] ?? 0) < 86400
    ));

    $message = "🔄 **Отчёт активности**\n"
        . "👥 Всего пользователей: ".count($users['users'] ?? [])."\n"
        . "💡 Активных за сутки: $activeUsers\n"
        . "⏰ Последнее обновление: ".date('H:i:s');

    file_get_contents(API_URL.'sendMessage?'.http_build_query([
        'chat_id' => CHANNEL_ID,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]));
    
    file_put_contents('last_report.txt', time());
}

// Проверка времени отправки отчёта
if (time() - filemtime('last_report.txt') > REPORT_INTERVAL) {
    sendActivityReport();
}

// Основная логика бота (ваш существующий код)
function processUpdate($update) {
    // ... ваш предыдущий код ...
    
    // Обновляем время активности пользователя
    $chat_id = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'];
    $users = json_decode(file_get_contents(USERS_FILE), true);
    $users['users'][$chat_id]['last_activity'] = time();
    file_put_contents(USERS_FILE, json_encode($users));
}

// Обработка входящих запросов
$content = file_get_contents("php://input");
if ($content) {
    $update = json_decode($content, true);
    processUpdate($update);
    
    // Отправляем отчёт при каждом обращении, если прошло больше интервала
    if (time() - filemtime('last_report.txt') > REPORT_INTERVAL) {
        sendActivityReport();
    }
}

// Ответ для Render health checks
echo "BOT IS RUNNING | Last report: ".date('H:i:s', filemtime('last_report.txt'));
?>