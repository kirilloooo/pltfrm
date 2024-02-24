<?php
if (strpos($text, "/ads") === 0 && isUserAdmin($chat_id)) {
    // Получение message_id из команды пользователя
    $commandParts = explode(" ", $text);
    if (count($commandParts) == 2) {
        $messageID = intval($commandParts[1]); // Получаем message_id из команды
        // Получение списка всех активных пользователей
        $userChatIDs = getFreeUserChatIDs();
        
        // ID чата, из которого вы хотите пересылать сообщения
        $fromChatID = '-100123456'; // Или ID канала
        
        // Пройдемся по списку пользователей и пересылаем сообщение каждому
        foreach ($userChatIDs as $chatID) {
            // Пересылаем сообщение пользователю
            Send("forwardMessage", [
                "chat_id" => $chatID,
                "from_chat_id" => $fromChatID,
                "message_id" => $messageID,
                "disable_notification" => true, // Можно добавить другие параметры, если нужно
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Invalid command format. Please use /ads [message_id]",
        ]);
    }
}

if (strpos($text, "/broadcast") === 0 && isUserAdmin($chat_id)) {
    // Получение message_id из команды пользователя
    $commandParts = explode(" ", $text);
    if (count($commandParts) == 2) {
        $messageID = intval($commandParts[1]); // Получаем message_id из команды
        // Получение списка всех активных пользователей
        $userChatIDs = getAllUserChatIDs();
        
        // ID чата, из которого вы хотите пересылать сообщения
        $fromChatID = '-100123456'; // Или ID канала
        
        // Пройдемся по списку пользователей и пересылаем сообщение каждому
        foreach ($userChatIDs as $chatID) {
            // Пересылаем сообщение пользователю
            Send("forwardMessage", [
                "chat_id" => $chatID,
                "from_chat_id" => $fromChatID,
                "message_id" => $messageID,
                "disable_notification" => true, // Можно добавить другие параметры, если нужно
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Invalid command format. Please use /broadcast [message_id]",
        ]);
    }
}