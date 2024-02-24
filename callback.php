<?php
// Callback query
if ($call) {
    $callKey = explode("*", $callData);

    // Delete user selected files
    if ($callKey[0] == "delete") {
        try {
            $fileID = $callKey[1];
            $sql = "SELECT * FROM files WHERE id ='$fileID' LIMIT 1";
            $q = $conn->query($sql);
            $r = $q->fetch(PDO::FETCH_ASSOC);
            $fileName = $r["file_name"];
            $fileDir = $r["file_dir"];
            $Delete = "DELETE FROM files WHERE id=$fileID";

            unlink("$fileDir/$fileName"); # Delete file in saved folder
            // Удаление папки, если она пуста
            if (is_dir($fileDir) && count(glob("$fileDir/*")) === 0) {
                rmdir($fileDir); // Удаление папки, если она пуста
            }
            $conn->exec($Delete); # Delete file in database
            updateUserFiles($chat_id, true); # Update user files count
            /*
            Send('deleteMessage',[
            'chat_id'           =>  $chat_id,
            'message_id'        =>  $message_id
            ]);
            */

            Send("editMessageText", [
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "parse_mode" => "HTML",
                "text" => $e["FileIsRemoved"],
            ]);

            Send("answerCallbackQuery", [
                "callback_query_id" => $callID,
                "text" => $e["Done"],
                "show_alert" => false,
            ]);
        } catch (PDOException $e) {
            Send("answerCallbackQuery", [
                "callback_query_id" => $callID,
                "text" => $e["Error"],
                "show_alert" => true,
            ]);
        }
    }
}

// Обработка колбэк-запросов для файлов
if ($call && strpos($callData, "file_") === 0) {
    $fileId = substr($callData, 5); // Получаем идентификатор файла из колбэк-запроса
    $fileInfo = getFileFromDB($fileId); // Получаем информацию о файле
    if ($fileInfo) {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "disable_web_page_preview" => "true",
            "parse_mode" => "HTML",
            "text" => $fileInfo["text"],
            "reply_markup" => json_encode([
                "inline_keyboard" => $fileInfo["buttons"],
            ]),
        ]);
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $e["fileNotFoundText"],
        ]);
    }
}

if (
    isset($call) &&
    $callData &&
    strpos($callData, "/my_files?page=") !== false
) {
    $page = explode("=", $callData)[1]; // Получаем номер страницы
    if (is_numeric($page)) {
        $currentPage = intval($page);

        // Обработка страницы для вывода файлов
        $fileList = getFileList($chat_id);
        if (!empty($fileList)) {
            $totalFiles = count($fileList); // Общее количество файлов
            $chunkedFiles = array_chunk($fileList, 14); // Разбиваем файлы на части
            $totalPages = count($chunkedFiles); // Общее количество страниц
            if ($currentPage >= 0 && $currentPage < $totalPages) {
                $filesForPage = $chunkedFiles[$currentPage];

                $buttonRows = splitFilesIntoTwoRows($filesForPage);

                // Подготовка кнопок для одного сообщения
                $inlineKeyboard = [];
                foreach ($buttonRows as $row) {
                    $inlineKeyboard[] = $row;
                }

                $paginationButtons = [];
                // Добавляем кнопки "вперед" и "назад" при необходимости
                if ($totalPages > 1) {
                    if ($currentPage > 0) {
                        $paginationButtons[] = [
                            "text" => $e["backBtn"],
                            "callback_data" =>
                                "/my_files?page=" . ($currentPage - 1),
                        ];
                    }

                    if ($currentPage < $totalPages - 1) {
                        $paginationButtons[] = [
                            "text" => $e["nextBtn"],
                            "callback_data" =>
                                "/my_files?page=" . ($currentPage + 1),
                        ];
                    }
                }

                $inlineKeyboard[] = $paginationButtons;

                // Сообщение с информацией о текущей странице, общем количестве страниц и файлов
                $message = $e["chooseFileText"] . "\n";
                $message .= $e["page"] . ($currentPage + 1) . "/$totalPages \n";
                $message .= "Total files: $totalFiles\n";

                Send("editMessageText", [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "message_id" => $message_id,
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $inlineKeyboard,
                    ]),
                ]);
            } else {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => $e["pageNotFoundText"],
                ]);
            }
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => $e["fileNotFoundText"],
            ]);
        }
    }
}

// Ban user
if ($callKey[0] == "ban") {
    try {
        $userId = $callKey[1];
        $banStatus = $callKey[2]; // Передается 0 или 1 для разблокировки/блокировки
        // Обновляем статус блокировки в базе данных
        $updateBan = $conn->prepare(
            "UPDATE users SET ban=:ban WHERE chat_id=:userId"
        );
        $updateBan->execute([
            ":ban" => $banStatus,
            ":userId" => $userId,
        ]);

        $banText = $banStatus == 1 ? "заблокирован" : "разблокирован";
        Send("answerCallbackQuery", [
            "callback_query_id" => $callID,
            "text" => "Пользователь $banText.",
            "show_alert" => true,
        ]);
    } catch (PDOException $e) {
        Send("answerCallbackQuery", [
            "callback_query_id" => $callID,
            "text" => $e->getMessage(),
            "show_alert" => true,
        ]);
    }
}

// Обработка нажатий на кнопки выбора тарифа
if (isset($call) && strpos($callData, "grant_plan") === 0) {
    $callParts = explode("*", $callData);
    if (count($callParts) === 3) {
        $userId = $callParts[1];
        $planId = $callParts[2];

        // SQL-запрос для обновления информации о тарифе пользователя в базе данных
        $updatePlanQuery = $conn->prepare(
            "UPDATE users SET plan=:planId WHERE chat_id=:userId"
        );
        $updatePlanQuery->bindParam(":planId", $planId);
        $updatePlanQuery->bindParam(":userId", $userId);
        $updatePlanQuery->execute();

        // Отправка сообщения об успешном изменении тарифа
        Send("sendMessage", [
            "chat_id" => $userId,
            "text" => "Your plan has been updated to Plan $planId.",
        ]);

        // Отправка уведомления о выполненном запросе
        Send("answerCallbackQuery", [
            "callback_query_id" => $callID,
            "text" => "Plan $planId granted to user $userId.",
            "show_alert" => true,
        ]);
    }
}

// Callback query
if ($call) {
    $callKey = explode("_", $callData);

    // Если нажата кнопка выбора тарифа
    if ($callKey[0] == "buy" && $callKey[1] == "plan") {
        $selectedPlan = $callKey[2]; // Получаем выбранный тариф

        // Получение информации о выбранном тарифе из базы данных или другого источника данных
        $planInfo = getPlanInfo($selectedPlan); // Функция для получения информации о тарифе из базы данных

        if ($planInfo) {
            // Формирование сообщения с информацией о тарифе и кнопкой "Назад"
            $replyMessage = "<b>Tariff selected:</b> {$planInfo['name']}\n";
            $replyMessage .= "<b>Description:</b> \n{$planInfo['description']}\n";
            $replyMessage .= "<b>Number of files:</b> {$planInfo['countFiles']}\n";
            $replyMessage .= "<b>Price:</b> {$planInfo['price']}\n";
            
            if ($selectedPlan == 2) {
                $tariffnamechoicetobuy = "gnome";
            } elseif ($selectedPlan == 4) {
                $tariffnamechoicetobuy = "hydra";
            } elseif ($selectedPlan == 5) {
                $tariffnamechoicetobuy = "phoenix";
            }

            // Создание кнопки "Назад" для возврата к списку тарифов
            $backButton = [
                [
                    "text" => $e["buy"] . ": " . $planInfo['name'],
                    "url" => "tg://resolve?domain=FujihiroBot&start={$tariffnamechoicetobuy}",
                ],
                [
                    "text" => "Back To Plans",
                    "callback_data" => "back_to_plans",
                ],
            ];

            Send("editMessageMedia", [
                "chat_id" => $chat_id,
                "message_id" => $message_id, // Идентификатор сообщения, которое вы хотите изменить
                "media" => json_encode([
                    "type" => "photo",
                    "media" => "https://api.kiro.pw/PLTFRM/tariff/index.php?name={$planInfo['name']}&files={$planInfo['countFiles']}&price={$planInfo['price']}&desc={$planInfo['description']}&temp=".date("Y-m-d"),
                    "caption" => $replyMessage, // Новая подпись
                    "parse_mode" => "HTML",
                ]),
                "reply_markup" => json_encode([
                    "inline_keyboard" => [$backButton],
                ]),
            ]);
        } else {
            // Если информация о тарифе не найдена, отправляем сообщение об ошибке
            Send("answerCallbackQuery", [
                "callback_query_id" => $callID,
                "text" => "No tariff information found.",
                "show_alert" => true,
            ]);
        }
    } elseif ($callData == "back_to_plans") {
        // Если нажата кнопка "Назад" из информации о тарифе, возвращаемся к списку тарифов
        $plansQuery = $conn->query("SELECT * FROM plans WHERE hide = 0");
        $plans = $plansQuery->fetchAll(PDO::FETCH_ASSOC);

        // Формирование сообщения со списком тарифов и кнопкой для каждого тарифа
        $replyMessage = "Available plans:\n";
        $inlineKeyboard = [];
        foreach ($plans as $plan) {
            $planName = $plan["name"];
            $planId = $plan["id"];

            $inlineKeyboard[] = [
                [
                    "text" => $planName,
                    "callback_data" => "buy_plan_$planId",
                ],
            ];
        }
        
        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id, // Идентификатор сообщения, которое вы хотите изменить
            "media" => json_encode([
                "type" => "photo",
                "media" => "https://api.kiro.pw/PLTFRM/tariff/tariff.png",
                "caption" => $replyMessage, // Новая подпись
            ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => $inlineKeyboard,
            ]),
        ]);

    }
}

// Обработка callback-команды для выбора тарифа
if (isset($call) && strpos($callData, "plan_select") === 0 && isUserAdmin($chat_id)) {
    $callParts = explode("*", $callData);
    if (count($callParts) === 3) {
        $userToGrant = $callParts[1];
        $planId = $callParts[2];

        // Создание кнопок для выбора длительности тарифа
        $durationKeyboard = [
            [
                ["text" => "1 month", "callback_data" => "plan_duration*$userToGrant*$planId*1"],
                ["text" => "3 months", "callback_data" => "plan_duration*$userToGrant*$planId*3"],
            ],
            [
                ["text" => "6 months", "callback_data" => "plan_duration*$userToGrant*$planId*6"],
                ["text" => "12 months", "callback_data" => "plan_duration*$userToGrant*$planId*12"],
            ],
        ];

        // Отправка сообщения с кнопками выбора длительности тарифа
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Please select the duration for the chosen plan to grant to user $userToGrant:",
            "reply_markup" => json_encode(["inline_keyboard" => $durationKeyboard]),
        ]);
    }
}

// Обработка callback-команды для выбора длительности тарифа
if (isset($call) && strpos($callData, "plan_duration") === 0 && isUserAdmin($chat_id)) {
    $callParts = explode("*", $callData);
    if (count($callParts) === 4) {
        $userToGrant = $callParts[1];
        $planId = $callParts[2];
        $duration = $callParts[3];

        // Вызов функции установки тарифного плана с указанной длительностью
        setTariffPlan($userToGrant, $planId, $duration);

        // Получение информации о тарифе из базы данных
        $planQuery = $conn->prepare("SELECT name FROM plans WHERE id=:plan_id");
        $planQuery->bindParam(":plan_id", $planId);
        $planQuery->execute();
        $planData = $planQuery->fetch(PDO::FETCH_ASSOC);

        if ($planData) {
            $planName = $planData["name"];

            // Отправка сообщения об успешном предоставлении тарифа с указанной длительностью
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Plan '$planName' granted to user $userToGrant for $duration months.",
            ]);

            // Отправка уведомления об успешном запросе
            Send("answerCallbackQuery", [
                "callback_query_id" => $callID,
                "text" => "Plan '$planName' granted to user $userToGrant for $duration months.",
                "show_alert" => true,
            ]);
        }
    }
}

if (
    isset($call) &&
    $callData &&
    strpos($callData, "menu_files?page=") !== false
) {
    $page = explode("=", $callData)[1]; // Получаем номер страницы
    if (is_numeric($page)) {
        $currentPage = intval($page);

        // Обработка страницы для вывода файлов
        $fileList = getFileList($chat_id);
        if (!empty($fileList)) {
            $totalFiles = count($fileList); // Общее количество файлов
            $chunkedFiles = array_chunk($fileList, 14); // Разбиваем файлы на части
            $totalPages = count($chunkedFiles); // Общее количество страниц
            if ($currentPage >= 0 && $currentPage < $totalPages) {
                $filesForPage = $chunkedFiles[$currentPage];

                $buttonRows = splitFilesIntoTwoRows($filesForPage);

                // Подготовка кнопок для одного сообщения
                $inlineKeyboard = [];
                foreach ($buttonRows as $row) {
                    $inlineKeyboard[] = $row;
                }

                $paginationButtons = [];
                // Добавляем кнопки "вперед" и "назад" при необходимости
                if ($totalPages > 1) {
                    if ($currentPage > 0) {
                        $paginationButtons[] = [
                            "text" => $e["backBtn"],
                            "callback_data" =>
                                "menu_files?page=" . ($currentPage - 1),
                        ];
                    }

                    if ($currentPage < $totalPages - 1) {
                        $paginationButtons[] = [
                            "text" => $e["nextBtn"],
                            "callback_data" =>
                                "menu_files?page=" . ($currentPage + 1),
                        ];
                    }
                }

                $inlineKeyboard[] = $paginationButtons;
                
                $menuButton[] = [
                    "text" => 'Menu',
                    "callback_data" => "menu",
                ];
        
                $inlineKeyboard[] = $menuButton;

                // Сообщение с информацией о текущей странице, общем количестве страниц и файлов
                $message = $e["chooseFileText"] . "\n";
                $message .= $e["page"] . ($currentPage + 1) . "/$totalPages \n";
                $message .= "Total files: $totalFiles\n";

                Send("editMessageMedia", [
                    "chat_id" => $chat_id,
                    "message_id" => $message_id,
                    "media" => json_encode([
                        "type" => "photo",
                        "media" => 'https://bot.site.com/123456Bot/img/files.png',
                        "caption" => $message, 
                        "parse_mode" => "HTML",
                    ]),
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $inlineKeyboard,
                        ]),
                ]);
            } else {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => $e["pageNotFoundText"],
                ]);
            }
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => $e["fileNotFoundText"],
            ]);
        }
    }
}


// Callback query for menu_files
if ($callData == "menu_files") {
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
                    "callback_data" => "menu_files?page=" . ($currentPage - 1),
                ];
            }

            if ($currentPage < count($chunkedFiles) - 1) {
                $paginationButtons[] = [
                    "text" => $e["nextBtn"],
                    "callback_data" => "menu_files?page=" . ($currentPage + 1),
                ];
            }
        }

        $inlineKeyboard[] = $paginationButtons;
        
        $menuButton[] = [
                    "text" => '🏠 Menu',
                    "callback_data" => "menu",
                ];
        
        $inlineKeyboard[] = $menuButton;

        // Создание сообщения с информацией о текущей странице и общем количестве страниц
        $message = $e["chooseFileText"] . "\n";
        $message .= $e["allFiles"] . $totalFiles . "\n";
        $message .=
            $e["page"] . ($currentPage + 1) . "/" . count($chunkedFiles) . "\n";
        
        $preliminaryMessage = Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => 'https://bot.site.com/123456Bot/img/files.png',
                "caption" => $message, 
                "parse_mode" => "HTML",
            ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => $inlineKeyboard,
                ]),
        ]);

        // Send("sendMessage", [
        //     "chat_id" => $chat_id,
        //     "text" => $message,
        //     "reply_markup" => json_encode([
        //         "inline_keyboard" => $inlineKeyboard,
        //     ]),
        // ]);
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $e["noFileText"],
        ]);
    }

}

// Callback query for menu_profile
if ($callData == "menu_profile") {
    
    // Получение информации о пользователе
    $userInfoQuery = $conn->prepare(
        "SELECT * FROM users WHERE chat_id = :chat_id"
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
        $preliminaryPhoto = "https://bot.site.com/123456Bot/img/loading.png";
        // $preliminaryMessage = Send("sendPhoto", [
        //     "chat_id" => $chat_id,
        //     "photo" => $preliminaryPhoto,
        //     "caption" => "Profile loading...", // Заглушка до получения реальной информации
        //     "parse_mode" => "HTML",
        // ]);


        $reply = "{$e['userInfo']}\n{$e['fullName']}\n";
        if ($username) {
            // Если есть юзернейм, добавить его в ответ
            $reply .= $e["userName"] . "\n";
        }
        $reply .= "{$e['totalFiles']} {$userFiles['fileCount']}/{$planInfo['countFiles']}\n";
        $reply .= "{$e['ratePlan']} {$planInfo['name']}\n";
        $reply .= "{$e['creationDate']} $userStartDate\n";
        
        if ($userInfo['planEnd'] == "0000-00-00") {
            $reply .= "{$e['rateEnd']} unlimited";
        } else {
            $leftTime = strtotime($userInfo['planEnd']) - strtotime(date("Y-m-d"));
            $leftTime = floor($leftTime / (60 * 60 * 24));
            $reply .= "{$e['rateEnd']} {$userInfo['planEnd']} ($leftTime left)";
        }
        
        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => $preliminaryPhoto,
                "caption" => $reply, 
                "parse_mode" => "HTML",
            ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                            "text" => '🏠 Menu',
                            "callback_data" => "menu",
                        ],
                    ],
                ],
            ]),
        ]);

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
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                            "text" => '🏠 Menu',
                            "callback_data" => "menu",
                        ],
                    ],
                ],
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

/*// Callback query for menu
if ($callData == "menu") {
    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/menu.png',
            "caption" => $e["welcome"] . "\n\n$randomText",
            "parse_mode" => "HTML",
            ]),
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [[
                    "text" => '👤 Profile',
                    "callback_data" => "menu_profile",
                    ],
                    [
                        "text" => '🗃 Files',
                        "callback_data" => "menu_files",
                    ],
                ],
                                [
                    [
                        "text" => '⚙️ Settings',
                        "callback_data" => "menu_settings",
                    ],
                ],
            ],
        ]),
        ]);
}*/

// Callback query for menu
if ($callData == "menu") {

    // Check if the message is older than 2 days (172800 seconds)
    if ($date = 0) {
        // Send a new message
        Send("sendPhoto", [
            "chat_id" => $chat_id,
            "photo" => 'https://bot.site.com/123456Bot/img/menu.png',
            "caption" => $e["welcome"] . "\n\n$randomText",
            "parse_mode" => "HTML",
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                            "text" => '👤 Profile',
                            "callback_data" => "menu_profile",
                        ],
                        [
                            "text" => '🗃 Files',
                            "callback_data" => "menu_files",
                        ],
                    ],
                    [
                        [
                            "text" => '⚙️ Settings',
                            "callback_data" => "menu_settings",
                        ],
                    ],
                ],
            ]),
        ]);
        // Show an alert that old messages can't be used
        Send("answerCallbackQuery", [
            "callback_query_id" => $callID,
            "text" => "Old messages can't be modified. A new menu has been sent.",
            "show_alert" => true,
        ]);
        logToFile('Time: '.$date);
        logToFile('Total: '. time() - $date);
    } else {
        // Edit the existing message
        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => 'https://bot.site.com/123456Bot/img/menu.png',
                "caption" => $e["welcome"] . "\n\n$randomText",
                "parse_mode" => "HTML",
            ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                            "text" => '👤 Profile',
                            "callback_data" => "menu_profile",
                        ],
                        [
                            "text" => '🗃 Files',
                            "callback_data" => "menu_files",
                        ],
                    ],
                    [
                        [
                            "text" => '⚙️ Settings',
                            "callback_data" => "menu_settings",
                        ],
                    ],
                ],
            ]),
        ]);
    }
}



// Callback query for menu_settings
if ($callData == "menu_settings") {
    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/settings.png',
            "caption" => "Settings\n\n$randomText",
            "parse_mode" => "HTML",
            ]),
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                    "text" => '🇺🇦 Language',
                    "callback_data" => "menu_settings_lang",
                    ],
                    [
                    "text" => '❌ Remove files',
                    "callback_data" => "danger_menu_files",
                    ],
                ],
                [
                    [
                    "text" => '🏠 Menu',
                    "callback_data" => "menu",
                    ],
                ],
            ],
        ]),
        ]);
}

// Callback query for menu_settings
if ($callData == "menu_settings_lang") {
    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/lang.png',
            "caption" => "🇺🇦 Take your language:",
            "parse_mode" => "HTML",
            ]),
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                    "text" => '🇬🇧 English',
                    "callback_data" => "lang_select*en",
                    ],
                ],
                [
                    [
                    "text" => '⬅️ Go Back',
                    "callback_data" => "menu_settings",
                    ],
                    [
                    "text" => '🏠 Menu',
                    "callback_data" => "menu",
                    ],
                ],
            ],
        ]),
        ]);
}

// Обработка callback-команды для выбора тарифа
if (isset($call) && strpos($callData, "lang_select") === 0) {
    $callParts = explode("*", $callData);
    if (count($callParts) === 2) {
        $language_select = $callParts[1];

        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => 'https://bot.site.com/123456Bot/img/lang.png',
                "caption" => "Language selected: " . $language_select,
                "parse_mode" => "HTML",
                ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                        "text" => '⬅️ Go Back',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => '🏠 Menu',
                        "callback_data" => "menu",
                        ],
                    ],
                ],
            ]),
        ]);
    
    $Update="UPDATE users SET lang=:lang WHERE chat_id=:chat_id";
    $ap=$conn->prepare($Update);
    $ap->bindParam(":lang",$language_select);
    $ap->bindParam(":chat_id",$chat_id);
    $ap->execute();
    
    }
}

// Callback query for danger_menu_files
if ($callData == "danger_menu_files") {
    
    $stmt = $conn->prepare("SELECT file_id FROM files WHERE user_id = :user_id");
    $stmt->execute([
        ":user_id" => $chat_id,
    ]);
    $fileList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalFiles = count($fileList);
    
    if ($totalFiles > 0) {
    
    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/danger.png',
            "caption" => "Once you press the DELETE button, you will NOT be able to abort the process.",
            "parse_mode" => "HTML",
            ]),
        "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                        "text" => 'NO.',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => '❌',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => 'NO.',
                        "callback_data" => "menu_settings",
                        ],
                    ],
                    [
                        [
                        "text" => '❌',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => 'DELETE',
                        "callback_data" => "full_delete_files",
                        ],
                        [
                        "text" => '❌',
                        "callback_data" => "menu_settings",
                        ],
                    ],
                    [
                        [
                        "text" => 'NO.',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => '❌',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => 'NO.',
                        "callback_data" => "menu_settings",
                        ],
                    ],
                    [
                        [
                        "text" => '⬅️ Go Back',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => '🏠 Menu',
                        "callback_data" => "menu",
                        ],
                    ],
                ],
            ]),
    ]);
    
    } else {
        Send("editMessageMedia", [
            "chat_id" => $chat_id,
            "message_id" => $message_id,
            "media" => json_encode([
                "type" => "photo",
                "media" => 'https://bot.site.com/123456Bot/img/settings.png',
                "caption" => "You dont have files",
                "parse_mode" => "HTML",
            ]),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                        "text" => '⬅️ Go Back',
                        "callback_data" => "menu_settings",
                        ],
                        [
                        "text" => '🏠 Menu',
                        "callback_data" => "menu",
                        ],
                    ],
                ],
            ]),
        ]);
    }

}


// Callback query for full_delete_files
if ($callData == "full_delete_files") {

    $deleteLimit = 10; // Установить лимит на количество файлов для удаления за одну итерацию

    $stmt = $conn->prepare("SELECT file_id FROM files WHERE user_id = :user_id");
    $stmt->execute([
        ":user_id" => $chat_id,
    ]);
    $fileList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalFiles = count($fileList);
    $filesLeft = $totalFiles;
    
    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/starting.png',
            "caption" => "After 5 seconds, the file deletion process will begin. After deletion - there will be no possibility to restore. Total files: $totalFiles",
            "parse_mode" => "HTML",
        ]),
    ]);
    
    sleep(5);

    foreach ($fileList as $file) {
        if ($filesLeft <= 0) {
            break; // Если больше нет файлов, прерываем цикл
        }

        $fileDirPath = "$tfolder/{$file['file_dir']}";
        $filePath = "$fileDirPath/{$file['file_name']}";

        // Удалить файл из базы данных
        $deleteFileQuery = $conn->prepare("DELETE FROM files WHERE file_id = :fileID");
        $deleteFileQuery->execute([':fileID' => $file['file_id']]);

        // Удалить файл
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Проверить пустая ли папка и удалить её, если пустая
        if (is_dir($fileDirPath) && count(glob("$fileDirPath/*")) === 0) {
            rmdir($fileDirPath);
        }

        $filesLeft--;
        
        if ($filesLeft % $deleteLimit === 0 || $filesLeft === 0) {
            // Отправить сообщение о количестве оставшихся файлов после каждой итерации
            Send("editMessageMedia", [
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "media" => json_encode([
                    "type" => "photo",
                    "media" => 'https://bot.site.com/123456Bot/img/deletion.png',
                    "caption" => "Files left: $filesLeft",
                    "parse_mode" => "HTML",
                ]),
            ]);
        }
        
        sleep(2);
    }

    Send("editMessageMedia", [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "media" => json_encode([
            "type" => "photo",
            "media" => 'https://bot.site.com/123456Bot/img/deleted.png',
            "caption" => "Your files have been successfully deleted. There's no way to get them back now. Files left: $filesLeft",
            "parse_mode" => "HTML",
        ]),
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => '⬅️ Go Back',
                        "callback_data" => "menu_settings",
                    ],
                    [
                        "text" => '🏠 Menu',
                        "callback_data" => "menu",
                    ],
                ],
            ],
        ]),
    ]);
}
