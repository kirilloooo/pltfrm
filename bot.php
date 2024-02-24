<?php
$INPUT = file_get_contents("php://input");
$update = json_decode($INPUT);
if ($update->message->chat->id) {
    $chat_id = $update->message->chat->id;
    $message_id = $update->message->message_id;
    $username = $update->message->from->username;
    $firstName = $update->message->from->first_name;
    $lastName = $update->message->from->last_name;
    $fullName = "$firstName $lastName";
    $lang = $update->message->from->language_code;
    $date = $update->message->date;
} else {
    $chat_id = $update->callback_query->message->chat->id;
    $message_id = $update->callback_query->message->message_id;
    $username = $update->callback_query->from->username;
    $firstName = $update->callback_query->from->first_name;
    $lastName = $update->callback_query->from->last_name;
    $fullName = "$firstName $lastName";
    $lang = $update->callback_query->from->language_code;
    $date = $update->callback_query->message->date;
}
$call = $update->callback_query;
$callData = $update->callback_query->data;
$callID = $update->callback_query->id;
$on = $update->message;
$text = $update->message->text;
$image = $update->message->photo;
$video = $update->message->video;
$videoNote = $update->message->video_note;
$music = $update->message->audio;
$voice = $update->message->voice;
$file = $update->message->document;
$uid = $chat_id;

// file_put_contents("message.txt", $INPUT); #Save Messages in: 'message.txt'
include_once __DIR__ . "/includes/settings.php";
include_once __DIR__ . "/includes/functions.php";

// Проверка блокировки пользователя перед выполнением любого действия
$userBanQuery = $conn->prepare(
    "SELECT ban FROM users WHERE chat_id = :chat_id"
);
$userBanQuery->bindParam(":chat_id", $chat_id);
$userBanQuery->execute();
$userBanStatus = $userBanQuery->fetch(PDO::FETCH_ASSOC);

if ($userBanStatus && $userBanStatus["ban"] == 1) {
    updateUserDetails($chat_id, $fullName, $username);
    firstInteraction($chat_id);
    updateOldInteractionDate($chat_id);
    // Пользователь заблокирован, отправляем ему сообщение о блокировке
    Send("sendMessage", ["chat_id" => $chat_id, "text" => $e["banText"]]);
    return;
}

/*  Messages
 *
 *   You can use HTML code. The following tags are currently supported:
 *
 *  <b>bold</b>, <strong>bold</strong>
 *  <i>italic</i>, <em>italic</em>
 *  <u>underline</u>, <ins>underline</ins>
 *  <s>strikethrough</s>, <strike>strikethrough</strike>, <del>strikethrough</del>
 *  <span class="tg-spoiler">spoiler</span>, <tg-spoiler>spoiler</tg-spoiler>
 *  <b>bold <i>italic bold <s>italic bold strikethrough <span class="tg-spoiler">italic bold strikethrough spoiler</span></s> <u>underline italic bold</u></i> bold</b>
 *  <a href="http://www.example.com/">inline URL</a>
 *  <a href="tg://user?id=123456789">inline mention of a user</a>
 *  <code>inline fixed-width code</code>
 *  <pre>pre-formatted fixed-width code block</pre>
 *  <pre><code class="language-python">pre-formatted fixed-width code block written in the Python programming language</code></pre>
 *
 *   $firstName      =>  Show user first name
 *   $lastName       =>  Show user last name
 *   $fullName       =>  Show user full name
 *   $username       =>  Show username
 *
 */

/*                 *
 *   Random text   *
 *                 */

$user_plan_id = getUserPlan($chat_id);
if ($user_plan_id < 3) {
    $randomTexts = [
        "This could be your advert. @myAlfredo",
        'Subscribe to the <a href="https://t.me/cookies_dev">Cookies Dev channel</a>',
    ]; // List of random texts
    $randomIndex = array_rand($randomTexts); // Choose a random index
    $randomText = $randomTexts[$randomIndex]; // Get the random text
    // Combine dashes and random text
    $combinedText = "\n\n-------------\n$randomText";
} else {
    // User has a plan of 3 or higher, so no random text is displayed
    $combinedText = '';
    $randomText = '';
}

include_once __DIR__ . "/broadcast.php";
include_once __DIR__ . "/callback.php";


/*
 * Commands
 */

// Send welcome message
if ($text == "/old_start") {
    updateUserDetails($chat_id, $fullName, $username);
    firstInteraction($chat_id);
    updateOldInteractionDate($chat_id);
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "disable_web_page_preview" => "true",
        "text" => $e["welcome"] . "\n\n$randomText",
    ]);
}

if ($text == "/start") {
    Send("sendPhoto", [
        "chat_id" => $chat_id,
        "photo" => 'https://bot.kiro.pw/FileWithURLBot/img/menu.png',
        "caption" => $e["welcome"] . "\n\n$randomText",
        "parse_mode" => "HTML",
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => '👤 Profile',
                        "callback_data" =>
                            "menu_profile",
                    ],
                    [
                        "text" => '🗃 Files',
                        "callback_data" =>
                            "menu_files",
                    ],
                ],
                                [
                    [
                        "text" => '⚙️ Settings',
                        "callback_data" =>
                            "menu_settings",
                    ],
                ],
            ],
        ]),
    ]);
}

// Обработка команды /promo и /start promo
if ($text == "/promo" || $text == "/start promo") {
    updateUserDetails($chat_id, $fullName, $username);
    firstInteraction($chat_id);
    updateOldInteractionDate($chat_id);

    // Получение даты регистрации пользователя
    $userRegistrationDate = getUserRegistrationDate($chat_id); // Замените на соответствующий метод
    $cutoffDate = strtotime("2024-1-15");

    if ($userRegistrationDate !== null && $userRegistrationDate < $cutoffDate) {
        // Получить план пользователя
        $userPlan = getUserPlan($chat_id);

        if ($userPlan !== null && $userPlan >= 0) {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => "true",
                "text" => "Get a promo code to activate a cooler tariff!",
                "reply_markup" => json_encode([
                    "inline_keyboard" => [
                        [
                            [
                                "text" => "Click Me!",
                                "url" =>
                                    "https://platform.eu.org/custom/winter/gift/promo.php?id=" .
                                    $chat_id,
                            ],
                        ],
                    ],
                ]),
            ]);
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" =>
                    "You are not eligible for a promo code at the moment.",
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" =>
                "You need to be registered before 15.01.2024 to access this command.",
        ]);
    }
}

if (strpos($text, "/code") === 0) {
    $promocode = trim(substr($text, 6));

    if (empty($promocode)) {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Please enter a promo code after /code.",
        ]);
    } else {
        processPromoCodes($chat_id, $promocode);
    }
}


// Commands
if ($text == "/help") {
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "disable_web_page_preview" => "true",
        "protect_content" => "true",
        "text" => $e["help"],
    ]);
}

if ($text == "/my_id") {
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "disable_web_page_preview" => "true",
        "text" => $chat_id,
    ]);
}

if ($text == "/up") {
    generateUniqueIDsForFilesWithoutIDs();
}

if ($text == "/partners") {
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "disable_web_page_preview" => "true",
        "protect_content" => "true",
        "text" => $e["partners"],
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => $e["TDriveBtnText"],
                        "url" =>
                            "https://apps.microsoft.com/store/detail/tdrive/9MVD1PKDTXSN?hl=en-us&gl=us",
                    ],
                ],
            ],
        ]),
    ]);
}

if ($text == "/error") {
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "text" => 'omg, you haven\'t access',
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => 'info',
                        "url" =>
                            "tg://some_unsupported_feature",
                    ],
                ],
            ],
        ]),
    ]);
}


if (strpos($text, "/get") === 0) {
    $file_id = trim(substr($text, 5));
    if (!empty($file_id)) {
        $fileInfo = getFileInfoFromDB($file_id, $chat_id);
        if ($fileInfo) {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => "true",
                "text" => $fileInfo["text"],
                "reply_markup" => json_encode([
                    "inline_keyboard" => $fileInfo["buttons"],
                ]),
            ]);
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "parse_mode" => "HTML",
                "text" => $e["fileNotFoundText"],
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "parse_mode" => "HTML",
            "text" => $e["writeFileIdText"],
        ]);
    }
} elseif (strpos($text, "/start") === 0) {
    if (
        $text === "/start promo" ||
        $text === "/start stats" ||
        $text === "/start statistics"
    ) {
    } else {
        $file_id = trim(substr($text, 7));
        if (!empty($file_id)) {
            $fileInfo = getFileInfoFromDB($file_id, $chat_id);
            if ($fileInfo) {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "parse_mode" => "HTML",
                    "disable_web_page_preview" => "true",
                    "text" => $fileInfo["text"],
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $fileInfo["buttons"],
                    ]),
                ]);
            } else {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "parse_mode" => "HTML",
                    "text" => $e["fileNotFoundText"],
                ]);
            }
        }
    }
}

// Обработка команды /me
if ($text === "/me") {
    // Получение информации о пользователе
    $userInfoQuery = $conn->prepare(
        "SELECT plan, oneStart, planEnd FROM users WHERE chat_id = :chat_id"
    );
    $userInfoQuery->bindParam(":chat_id", $chat_id);
    $userInfoQuery->execute();
    $userInfo = $userInfoQuery->fetch(PDO::FETCH_ASSOC);

    if ($userInfo) {
        $userPlan = $userInfo["plan"];
        $userStartDate = $userInfo["oneStart"];

        // Получение информации о тарифном плане пользователя
        $userPlanInfoQuery = $conn->prepare(
            "SELECT * FROM plans WHERE id = :plan_id"
        );
        $userPlanInfoQuery->bindParam(":plan_id", $userPlan);
        $userPlanInfoQuery->execute();
        $planInfo = $userPlanInfoQuery->fetch(PDO::FETCH_ASSOC);

        // Получение общего количества файлов пользователя
        $userFilesQuery = $conn->prepare(
            "SELECT COUNT(*) as fileCount FROM files WHERE user_id = :user_id"
        );
        $userFilesQuery->bindParam(":user_id", $chat_id);
        $userFilesQuery->execute();
        $userFiles = $userFilesQuery->fetch(PDO::FETCH_ASSOC);
        
        
        // 1. Отправка предварительного изображения
        $preliminaryPhoto = "https://bot.kiro.pw/FileWithURLBot/img/loading.png";

        $reply = "{$e["userInfo"]}\n{$e["fullName"]}\n";
        if ($username) {
            // Если есть юзернейм, добавить его в ответ
            $reply .= $e["userName"] . "\n";
        }
        $reply .= "{$e["totalFiles"]} {$userFiles["fileCount"]}/{$planInfo["countFiles"]}\n";
        $reply .= "{$e['ratePlan']} {$planInfo['name']}\n";
        $reply .= "{$e['creationDate']} $userStartDate\n";
        
        if ($userInfo['planEnd'] == "0000-00-00") {
            $reply .= "{$e['rateEnd']} unlimited";
        } else {
            $leftTime = strtotime($userInfo['planEnd']) - strtotime(date("Y-m-d"));
            $leftTime = floor($leftTime / (60 * 60 * 24));
            $reply .= "{$e['rateEnd']} {$userInfo['planEnd']} ($leftTime left)";
        }

        $preliminaryMessage = Send("sendPhoto", [
            "chat_id" => $chat_id,
            "photo" => $preliminaryPhoto,
            "caption" => $reply, // Заглушка до получения реальной информации
            "parse_mode" => "HTML",
        ]);
        // Получите идентификатор сообщения
        $message_id = $preliminaryMessage->result->message_id;


        // Загрузка изображения
        $photo =
            "https://api.kiro.pw/PLTFRM/index.php?name=" .
            $firstName .
            "&files=" .
            $userFiles["fileCount"] .
            "/" .
            $planInfo["countFiles"] .
            "&plan=" .
            $planInfo["name"] .
            "&date=" .
            $userStartDate .
            "&temp=" .
            time();

        // Отправка сообщения с информацией о файлах пользователя и его тарифном плане
        /*Send("sendPhoto", [
            "chat_id" => $chat_id,
            "photo" => $photo,
            "caption" => $reply,
            "parse_mode" => "HTML",
        ]);*/
        

        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => $photo,
                "caption" => $reply, // Новая подпись с фактической информацией
                "parse_mode" => "HTML",
            ]),
        ]);


    } else {
        // Если информация о пользователе не найдена
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $e["noUserText"],
        ]);
    }
}

if (
    $text == "/stats" ||
    $text == "/start statistics"
) {
    $statistics = getStats();
    $userCount = $statistics["userCount"];
    $fileCount = $statistics["fileCount"];

    // Загрузка изображения
    $photo =
        "AgACAgIAAxkBAAIoRWWThzNXFknlWcle-DkXlJpushijAAKF0zEbIn2ZSJ5pfrwAAd9DmQEAAwIAA3kAAzQE"; // замените на фактическую ссылку на изображение

    Send("sendPhoto", [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "photo" => $photo, // Укажите переменную с фото или ссылку на изображение
        "caption" =>
            $e["stats"] .
            $e["stats1"] .
            "<b><i>" .
            $userCount .
            "</i></b>" .
            $e["stats2"] .
            "<b><i>" .
            $fileCount .
            "</i></b>",
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => $e["btn_stats"],
                        "web_app" => ["url" => "https://platform.eu.org/stats"],
                    ],
                ],
            ],
        ]),
    ]);
}

// Обработка команды /paid
if ($text === "/paid") {
    // Запрос к базе данных для получения информации о тарифных планах
    $plansQuery = $conn->query("SELECT * FROM plans WHERE hide = 0");
    $plans = $plansQuery->fetchAll(PDO::FETCH_ASSOC);

    $reply = "Available plans:\n";

    $keyboard = [];
    foreach ($plans as $plan) {
        $planName = $plan["name"];
        $planId = $plan["id"];
        $keyboard[] = [
            [
                "text" => $planName,
                "callback_data" => "buy_plan_" . $planId,
            ],
        ];
    }
    
    Send("sendPhoto", [
        "chat_id" => $chat_id,
        "photo" => "https://api.kiro.pw/PLTFRM/tariff/tariff.png", // Замените ссылкой на ваше изображение
        "caption" => $reply, // Текст сообщения с информацией о тарифе
        "parse_mode" => "HTML",
        "reply_markup" => json_encode([
            "inline_keyboard" => $keyboard,
        ]),
    ]);
}

if ($text == "/emoji") {
    $emojiMessage = "Emojis: \n";

    foreach ($randomEmojis as $emoji) {
        $emojiMessage .= $emoji . " ";
    }

    Send("sendMessage", [
        "chat_id" => $chat_id,
        "disable_web_page_preview" => "true",
        "protect_content" => "true",
        "text" => $emojiMessage,
    ]);
}

if ($text == "/update_commands") {
    updateCommands($chat_id);
}

// Обработка команды /my_files
if ($text == "/my_files") {
    $fileList = getFileList($chat_id);

    if (!empty($fileList)) {
        $totalFiles = count($fileList); // Общее количество файлов
        $chunkedFiles = array_chunk($fileList, 14); // Разбиваем файлы на части
        // Получаем текущую страницу из параметров запроса или устанавливаем первую страницу
        $currentPage = isset($text["page"]) ? intval($text["page"]) : 0;

        if ($currentPage < 0) {
            $currentPage = 0;
        } elseif ($currentPage >= count($chunkedFiles)) {
            $currentPage = count($chunkedFiles) - 1;
        }

        $filesForPage = $chunkedFiles[$currentPage];

        $buttonRows = splitFilesIntoTwoRows($filesForPage);

        // Подготовка кнопок для одного сообщения
        $inlineKeyboard = [];
        foreach ($buttonRows as $row) {
            $inlineKeyboard[] = $row;
        }

        $paginationButtons = [];
        // Добавляем кнопки "вперед" и "назад" при необходимости
        if (count($chunkedFiles) > 1) {
            if ($currentPage > 0) {
                $paginationButtons[] = [
                    "text" => $e["backBtn"],
                    "callback_data" => "/my_files?page=" . ($currentPage - 1),
                ];
            }

            if ($currentPage < count($chunkedFiles) - 1) {
                $paginationButtons[] = [
                    "text" => $e["nextBtn"],
                    "callback_data" => "/my_files?page=" . ($currentPage + 1),
                ];
            }
        }

        $inlineKeyboard[] = $paginationButtons;

        // Создание сообщения с информацией о текущей странице и общем количестве страниц
        $message = $e["chooseFileText"] . "\n";
        $message .= $e["allFiles"] . $totalFiles . "\n";
        $message .=
            $e["page"] . ($currentPage + 1) . "/" . count($chunkedFiles) . "\n";

        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $message,
            "reply_markup" => json_encode([
                "inline_keyboard" => $inlineKeyboard,
            ]),
        ]);
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $e["noFileText"],
        ]);
    }
}

// Check if the command starts with '/grant_plan'
if (strpos($text, "/grant_plan") === 0 && isUserAdmin($chat_id)) {
    $commandParts = explode(" ", $text);
    if (count($commandParts) >= 2) {
        $userToGrant = $commandParts[1];

        // Получение данных о тарифах из базы данных
        $planQuery = $conn->prepare("SELECT id, name FROM plans");
        $planQuery->execute();
        $plans = $planQuery->fetchAll(PDO::FETCH_ASSOC);

        if ($plans) {
            // Создание кнопок для выбора тарифа
            $keyboard = [];
            foreach ($plans as $plan) {
                $keyboard[] = [
                    [
                        "text" => $plan["name"],
                        "callback_data" =>
                            "grant_plan*" . $userToGrant . "*" . $plan["id"],
                    ],
                ];
            }

            // Отправка сообщения с кнопками выбора тарифа
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Please select the plan you want to grant to user $userToGrant:",
                "reply_markup" => json_encode(["inline_keyboard" => $keyboard]),
            ]);
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "No plans found in the database.",
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Please provide the user ID after /grant_plan.",
        ]);
    }
}

// Команда для выдачи тарифа пользователю с интеграцией callback-запросов
if (strpos($text, "/grant") === 0 && isUserAdmin($chat_id)) {
    $commandParts = explode(" ", $text);
    if (count($commandParts) >= 2) {
        $userToGrant = $commandParts[1];

        // Получение данных о тарифах из базы данных для выбора
        $planQuery = $conn->prepare("SELECT id, name FROM plans");
        $planQuery->execute();
        $plans = $planQuery->fetchAll(PDO::FETCH_ASSOC);

        if ($plans) {
            // Создание кнопок для выбора тарифа
            $keyboard = [];
            foreach ($plans as $plan) {
                $keyboard[] = [
                    [
                        "text" => $plan["name"],
                        "callback_data" =>
                            "plan_select*$userToGrant*" . $plan["id"],
                    ],
                ];
            }

            // Отправка сообщения с кнопками выбора тарифа
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Please select the plan you want to grant to user $userToGrant:",
                "reply_markup" => json_encode(["inline_keyboard" => $keyboard]),
            ]);
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "No plans found in the database.",
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Please provide the user ID after /grant_plan.",
        ]);
    }
}

if (strpos($text, "/rename_") === 0) {
    $params = explode(" ", $text, 2); // Получаем параметры команды (/rename_file_id new_file_name)
    if (count($params) === 2) {
        $originFileName = explode("_", $params[0]);
        if (count($originFileName) === 2) {
            $origin_file_id = trim($originFileName[1]);
            $newFileName = trim($params[1]);

            $renameResult = renameFileByOriginFileId($origin_file_id, $newFileName); // Переименовываем файл

            if (is_string($renameResult)) {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => $renameResult, // Выводим сообщение об ошибке или успехе операции
                ]);
            } elseif ($renameResult) {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => "File renamed successfully to: $newFileName",
                ]);
            } else {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => "Failed to rename the file. Please check the origin file ID and try again.",
                ]);
            }
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Invalid format. Use: /rename_file_id newFile_name123",
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Invalid format. Use: /rename_file_id newFile_name123",
        ]);
    }
}

// Place this within your code where commands are handled
if ($text == "/saveids" && isUserAdmin($chat_id) || $text == "юзеры" && isUserAdmin($chat_id)) {
    saveUserIDsToFile();
    Send("sendMessage", [
        "chat_id" => $chat_id,
        "text" => "<code>https://bot.kiro.pw/FileWithURLBot/user_ids.txt</code> \nUse @UploadBot for telegram use",
        "parse_mode" => "HTML",
    ]);
}



/*
 * FILE SAVE
 */

// Save images
if (isset($on->reply_to_message)) {}
else if ($image) {
    if ($image[3]) {
        $fileID = $image[3]->file_id;
    } elseif ($image[2]) {
        $fileID = $image[2]->file_id;
    } elseif ($image[1]) {
        $fileID = $image[1]->file_id;
    } elseif ($image[0]) {
        $fileID = $image[0]->file_id;
    }

    $fileSaveData = save($fileID, $imageFolder); # Save file
    if ($fileSaveData) {
        updateUserFiles($chat_id); # Update user files count
        $res = Send("sendMessage", [
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "parse_mode" => "HTML",
            "disable_web_page_preview" => "true",
            "text" =>
                $e["imageSaveText"] . $fileSaveData["text"] . $combinedText,
            "reply_markup" => json_encode([
                "inline_keyboard" => $fileSaveData["buttons"],
            ]),
        ]);

        saveMessageIdFiles($fileID, $res->result->message_id);
        updateUserDetails($chat_id, $fullName, $username);
    }
}

// Save videos & video notes
if (isset($on->reply_to_message)) {}
else if ($video || $videoNote)
{

    if ($video)
    {
        $fileID = $video->file_id;
        $fileSize = ($video->file_size / 1024) / 1024;
    }
    else
    {
        $fileID = $videoNote->file_id;
        $fileSize = ($videoNote->file_size / 1024) / 1024;
    }

    if ((int)$fileSize > 20)
    {
        Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['FileIsBig'], ]);
    }
    else
    {
        $fileSaveData = save($fileID, $videoFolder); # Save file
        updateUserFiles($chat_id); # Update user files count
        if ($fileSaveData)
        {
            $res = Send('sendMessage', ['chat_id' => $chat_id, 'reply_to_message_id' => $message_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => 'true', 'text' => $e['videoSaveText'] . $fileSaveData['text'] . $combinedText, 'reply_markup' => json_encode(['inline_keyboard' => $fileSaveData['buttons'], ]) , ]);

            saveMessageIdFiles($fileID, $res
                ->result
                ->message_id);
            updateUserDetails($chat_id, $fullName, $username);
        }
        else
        {
            Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['errorToSave'] . '3', ]);

        }
    }
}

// Save musics & voices
if (isset($on->reply_to_message)) {}
else if ($music || $voice)
{

    if ($music)
    {
        $fileID = $music->file_id;
        $fileSize = ($music->file_size / 1024) / 1024;
    }
    else
    {
        $fileID = $voice->file_id;
        $fileSize = ($voice->file_size / 1024) / 1024;
    }

    if ((int)$fileSize > 20)
    {
        Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['FileIsBig'], ]);
    }
    else
    {
        $fileSaveData = save($fileID, $musicFolder); # Save file
        updateUserFiles($chat_id); # Update user files count
        if ($fileSaveData)
        {
            $res = Send('sendMessage', ['chat_id' => $chat_id, 'reply_to_message_id' => $message_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => 'true', 'text' => $e['musicSaveText'] . $fileSaveData['text'] . $combinedText, 'reply_markup' => json_encode(['inline_keyboard' => $fileSaveData['buttons'], ]) , ]);

            saveMessageIdFiles($fileID, $res
                ->result
                ->message_id);
            updateUserDetails($chat_id, $fullName, $username);
        }
        else
        {
            Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['errorToSave'] . '4', ]);
        }
    }
}

// Save documents
if (isset($on->reply_to_message)) {}
else if ($file)
{

    $fileID = $file->file_id;
    $fileSize = ($file->file_size / 1024) / 1024;

    if ((int)$fileSize >= 20)
    {
        Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['FileIsBig'], ]);
    }
    else
    {
        $fileSaveData = save($fileID, $fileFolder); #Save file
        updateUserFiles($chat_id); # Update user files count
        if ($fileSaveData)
        {
            $res = Send('sendMessage', ['chat_id' => $chat_id, 'reply_to_message_id' => $message_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => 'true', 'text' => $e['fileSaveText'] . $fileSaveData['text'] . $combinedText, 'reply_markup' => json_encode(['inline_keyboard' => $fileSaveData['buttons'], ]) , ]);

            saveMessageIdFiles($fileID, $res
                ->result
                ->message_id);
            updateUserDetails($chat_id, $fullName, $username);
        }
        else
        {
            logToFile('Error: File not saved.');
        }
    }
}

// Insert & Update User
if (isset($on)) {
    $stmt = $conn->prepare("SELECT chat_id FROM users WHERE chat_id=:chat_id");
    $stmt->execute([
        ":chat_id" => $chat_id,
    ]);
    $count = $stmt->rowCount();
    $time = time();
    if ($count > 0) {
        $userUpdate = "UPDATE users SET full_name='$fullName', username='$username', date='$time', active='1' WHERE chat_id=$chat_id";
        $ap = $conn->prepare($userUpdate);
        $ap->execute();
    } else {
        try {
            $userSet =
                "INSERT INTO `users` (`chat_id`, `full_name`, `username`, `date`, `active`) VALUES (:chat_id, :full_name, :username, :date, :active)";
            $userInsert = $conn->prepare($userSet);
            $userInsert->execute([
                ":chat_id" => $chat_id,
                ":full_name" => "$fullName",
                ":username" => "$username",
                ":date" => time(),
                ":active" => 1, 
            ]);
        } catch (PDOException $e) {
            file_put_contents("Error_log.txt", $e->getMessage()); # Save Errors in file: 'Error_log.txt'
        }
    }
}

// For cron job
if ($DELETEFILES && $deleteFilesMessageID) {
    editMessageFileDeleted($userID, $deleteFilesMessageID);
}

#===============================================================================
#-------------------------------- Webhook --------------------------------------
#===============================================================================
if (isset($_GET["setWebhook"])) {
    $publicKEY = ""; # *Optional* - Public key certificate file.
    $url = APP_URL . "/setWebhook?url=" . curPageURL(true);

    // Check for optional parameters
    if (isset($_GET["drop_pending_updates"])) {
        $url .= "&drop_pending_updates=" . $_GET["drop_pending_updates"];
    }
    if (isset($_GET["max_connections"])) {
        $url .= "&max_connections=" . $_GET["max_connections"];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    if (!empty($publicKEY)) {
        $post = [
            "certificate" => new CURLFile(realpath($publicKEY)),
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    curl_exec($ch);
    curl_close($ch);
}

if (isset($_GET["getWebhookInfo"])) {
    $url = APP_URL . "/getWebhookInfo";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
    curl_close($ch);
}

if (isset($_GET["deleteWebhook"])) {
    $url = APP_URL . "/deleteWebhook";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
    curl_close($ch);
}
