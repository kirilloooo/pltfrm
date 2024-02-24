<?php

include_once __DIR__ . '/includes/settings.php';
include_once __DIR__ . '/includes/functions.php';

// Функция для отправки сообщения
function sendBroadcastMessage($message, $inlineKeyboard)
{
    global $conn;

    // Получить все чат-ид пользователей
    $userChatIDs = getAllUserChatIDs();

    // Отправить сообщение с медиафайлом и кнопками каждому пользователю с задержкой
    $batchSize = 30; // Отправлять сообщения пакетами по 30 человек
    $delayBetweenBatches = 5; // Задержка между пакетами в секундах
    
    $userBatches = array_chunk($userChatIDs, $batchSize); // Разделить пользователей на пакеты
    
    foreach ($userBatches as $batch) {
        foreach ($batch as $userID) {
            $textData = [
                'chat_id' => $userID,
                'text' => $message,
                'parse_mode' => 'HTML', // Если требуется форматирование текста
                'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard]) // Кнопки
            ];
    
            // Отправить текстовое сообщение с кнопками каждому пользователю
            Send('sendMessage', $textData);
            sleep(1); // Задержка между отправкой сообщений каждому пользователю
        }
        sleep($delayBetweenBatches); // Задержка между пакетами
    }
}

// Текст рассылки
$broadcastMessage = "Stay tuned to our bot channel for more news!!1";

// Конфигурация кнопок
$inlineKeyboard = [
    [
        ['text' => 'Join', 'url' => 'tg://join?invite=5156165']
    ],
    // Добавьте больше строк/кнопок при необходимости
];

// Отправить сообщение с кнопками всем пользователям
sendBroadcastMessage($broadcastMessage, $inlineKeyboard);

?>
