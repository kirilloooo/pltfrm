<?php
function logToFile($message) {
    $logFile = 'error.log'; // Имя файла лога
    $timestamp = date('Y-m-d H:i:s'); // Текущее время

    // Формируем строку для записи в файл лога
    $logMessage = "$timestamp: $message" . PHP_EOL;

    // Добавляем сообщение в файл лога
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function Send($method, array $data)
{
    $url = APP_URL . "/$method";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $res = curl_exec($ch);

    if ($res === false) {
        // Если сообщение не было доставлено, вернуть null
        return null;
    }

    curl_close($ch);
    return json_decode($res);
}

function isFileOwner($origin_file_id, $user_id)
{
    global $conn;
    $stmt = $conn->prepare('SELECT user_id FROM files WHERE origin_file_id = :origin_file_id AND user_id = :user_id');
    $stmt->execute(array(':origin_file_id' => $origin_file_id, ':user_id' => $user_id));
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    return $file !== false;
}

function isUserAdmin($chat_id)
{
    $admin_ids = [123456789 /* другие chat_id администраторов */];
    return in_array($chat_id, $admin_ids);
}

function getFileFromDB($file_id)
{
    global $conn, $chat_id, $e;

    $stmt = $conn->prepare(
        "SELECT * FROM files WHERE origin_file_id=:file_id AND  user_id=:user_id"
    );
    $stmt->execute([
        ":file_id" => $file_id,
        ":user_id" => $chat_id,
    ]);
    $count = $stmt->rowCount();

    if ($count > 0) {
        $fileData = $stmt->fetch(PDO::FETCH_ASSOC);
        $file_id = $fileData["origin_file_id"];
        $file_dir = $fileData["file_dir"];
        $file_name = $fileData["file_name"];

        $fileInfoText = "File Name: <b>{$fileData["file_rename"]}</b>\nFile ID: <b><a href='https://t.me/12345bot?start={$fileData["origin_file_id"]}'>{$fileData["origin_file_id"]}</a></b>\nLink: <code>https://go.short.com/{$fileData["origin_file_id"]}</code>\nRename: <b>/rename_{$fileData["origin_file_id"]}</b>";
        $filePageUrl =
            "https://site.org/preview?id=" . $fileData["file_id"];

        $FileBtns = [
            [
                [
                    "text" => $e["DownloadBtnText"],
                    "url" =>
                        "https://go.short.com/" .
                        $file_id,
                ],
                [
                    "text" => $e["DeleteBtnText"],
                    "callback_data" => "delete*" . $fileData["id"],
                ],
            ],
            [["text" => "🍳 Preview", "web_app" => ["url" => $filePageUrl]]],
        ];

        return ["text" => $fileInfoText, "buttons" => $FileBtns];
    }

    return false;
}

function getFileInfoFromDB($file_id, $user_id)
{
    global $conn, $e;

    $stmt = $conn->prepare("SELECT * FROM files WHERE origin_file_id=:file_id");
    $stmt->execute([
        ":file_id" => $file_id,
    ]);
    $fileData = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $stmt->rowCount();

    if ($count > 0) {
        $fileOwnerID = $fileData["user_id"];
        $isAdmin = isUserAdmin($user_id);

        $fileInfoText = "File Name: {$fileData["file_rename"]}\nFile ID: <a href='https://t.me/12345bot?start={$fileData["origin_file_id"]}'>{$fileData["origin_file_id"]}</a>\nLink: <code>https://go.short.com/{$fileData["origin_file_id"]}</code>";

        $FileBtns = [
            [
                [
                    "text" => $e["DownloadBtnText"],
                    "url" =>
                        "https://go.short.com/{$fileData["origin_file_id"]}",
                ],
            ],
        ];

        if ($isAdmin || $user_id === $fileOwnerID) {
            $FileBtns[0][] = [
                "text" => $e["DeleteBtnText"],
                "callback_data" => "delete*" . $fileData["id"],
            ];
        }

        if ($isAdmin) {
            $FileBtns[0][] = [
                "text" => $e["BanUserBtnText"],
                "callback_data" => "ban*" . $fileOwnerID . "*1",
            ];
        }

        $filePageUrl =
            "https://site.org/preview?id=" . $fileData["file_id"];
        $FileBtns[] = [
            ["text" => "🍳 Preview", "web_app" => ["url" => $filePageUrl]],
        ];

        return ["text" => $fileInfoText, "buttons" => $FileBtns];
    }

    return false;
}

function curPageURL($fullPageUrl = false)
{
    $pageURL = "http";
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    $pageName = substr($pageURL, strrpos($pageURL, "/") + 1);
    $pageGet = substr($pageName, strrpos($pageName, "?"));
    if ($fullPageUrl) {
        return str_replace($pageGet, "", $pageURL);
    }
    return str_replace($pageName, "", $pageURL);
}

function getUserPlan($user_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT plan FROM users WHERE chat_id = :user_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $userPlan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userPlan) {
        return $userPlan["plan"]; // Return the plan ID
    } else {
        return null; // User not found or plan not set
    }
}

function generateRandomCode($length = 12)
{
    $characters =
        "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $fullCharacters =
        "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $code = "";
    $maxIndex = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, $maxIndex)];
    }
    return $code;
}

function save($fileID, $filePath)
{
    logToFile('Save function started.');
    global $conn, $chat_id, $e, $folder, $tfolder;
    $stmt = $conn->prepare('SELECT file_id FROM files WHERE file_id=:file_id');
    $stmt->execute(array(
        ':file_id' => $fileID
    ));
    $count = $stmt->rowCount();

    // Get the user's plan details
    $userPlanQuery = $conn->prepare('SELECT plan FROM users WHERE chat_id=:chat_id');
    $userPlanQuery->execute(array(
        ':chat_id' => $chat_id
    ));
    $userPlanResult = $userPlanQuery->fetch(PDO::FETCH_ASSOC);


    if (!$userPlanResult)
    {
        return false; // User not found, abort file upload
        
    }

    $planID = $userPlanResult['plan'];

    // Get the plan's allowed file count from the plans table
    $planDetailsQuery = $conn->prepare('SELECT countFiles FROM plans WHERE id=:plan');
    $planDetailsQuery->execute(array(
        ':plan' => $planID
    ));
    $planDetails = $planDetailsQuery->fetch(PDO::FETCH_ASSOC);

    if (!$planDetails)
    {
        return false; // Plan details not found, abort file upload
        
    }

    $allowedFileCount = $planDetails['countFiles'];

    // Check the number of files already uploaded by the user
    $stmtCount = $conn->prepare('SELECT COUNT(*) as fileCount FROM files WHERE user_id=:user_id');
    $stmtCount->execute(array(
        ':user_id' => $chat_id
    ));
    $fileCount = $stmtCount->fetch(PDO::FETCH_ASSOC) ['fileCount'];

    if ($fileCount >= $allowedFileCount)
    {
        Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['maxLimit'] . $allowedFileCount . $e['maxLimit2']]);
        return false; // Abort the file upload process
        
    }

    if ($count > 0)
    {

        $sql = "SELECT * FROM files WHERE file_id ='$fileID' LIMIT 1";
        $q = $conn->query($sql);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        $file_id = $r['id'];
        $file_name = $r['file_name'];
        $file_dir = $r['file_dir'];
        $file_origin_url = curPageURL() . "$file_dir/$file_name";
        $file1_url = "https://go.short.com/" . $r["origin_file_id"];

        $FileBtns = [[['text' => $e['DownloadBtnText'], 'url' => $file1_url]], [['text' => $e['DeleteBtnText'], 'callback_data' => 'delete*' . $file_id]]];

        return $FileBtns;
    }
    else
    {
        $dir = "$folder/$filePath/$fileID";
        $tdir = "$tfolder/$dir";
        $URL = APP_URL . "/getFile?file_id=$fileID";
        $a = file_get_contents($URL);
        $c = json_decode($a, true);
        $path = $c['result']['file_path'];
        $fileSize = $c['result']['file_size'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (empty($extension))
        {  
            $ext = substr($path, strrpos($path, '/'));
            $ext2 = str_replace($ext, '', $path);
            if ($ext2 == 'video_notes')
            {
                $extension = 'mp4';
            }
            if ($ext2 == 'voice')
            {
                $extension = 'ogg';
            }
        }
        $URL1 = APP_URL . "/getFile?file_id=$fileID";
        $a1 = file_get_contents($URL1);
        $c1 = json_decode($a1, true);
        $file_unique_id = $c1['result']['file_unique_id'];
        $fileName = $file_unique_id . rand(1, 9999) . "." . $extension;

        
        // Generate a unique code
        $uniqueCode = generateRandomCode();

        $checkCodeQuery = $conn->prepare('SELECT COUNT(*) as codeCount FROM files WHERE origin_file_id = :unique_code');
        $checkCodeQuery->execute(array(
            ':unique_code' => $uniqueCode
        ));
        $codeCount = $checkCodeQuery->fetch(PDO::FETCH_ASSOC)['codeCount'];

        while ($codeCount > 0) {
            $uniqueCode = generateRandomCode(); // Regenerate the code
            $checkCodeQuery->execute(array(
                ':unique_code' => $uniqueCode
            ));
            $codeCount = $checkCodeQuery->fetch(PDO::FETCH_ASSOC)['codeCount'];
        }

        $downloadPath = 'https://api.telegram.org/file/bot' . API_KEY . "/$path";
        
        // Create Folder
        if (is_dir($tdir) == false)
        {
            logToFile('not found dir');
            if (!mkdir($tdir, 0777, true))
            {
                Send('sendMessage', ['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $e['errorMakeFolder']]);
                logToFile('Error: Failed to make folder.');
                return false;
            }
        }

        // Save File
        if (!copy($downloadPath, $fileName))
        {
            logToFile('Error: Failed to copy file.');
            return false;
        }
        
        //Move File To Folder
        else if (!rename($fileName, "$tdir/$fileName"))
        {
            logToFile('Error: Failed to rename file.');
            return false;
        }
        else
        {  
            $owner_plan = getUserPlan($chat_id);
            $set = 'INSERT INTO `files` (`file_id`, `file_name`, `file_dir`, `file_size`, `user_id`, `date`, `origin_file_id`, `owner_plan`, `file_rename`) VALUES (:file_id, :file_name, :file_dir, :file_size, :user_id, :date, :origin_file_id, :owner_plan, :file_rename)';
            $insert = $conn->prepare($set);
            $insert->execute(array(
                ':file_id' => $fileID,
                ':file_name' => $fileName,
                ':origin_file_id' => $uniqueCode, // Insert the unique code into the database
                ':file_dir' => $dir,
                ':file_size' => $fileSize,
                ':user_id' => $chat_id,
                ':date' => time(),
                ':owner_plan' => $owner_plan, // Save the user's plan ID with the file
                ':file_rename' => $fileName, // Save the original file name in file_rename
            ));

            $file_id = $conn->lastInsertId();

            $file_origin_url = curPageURL() . "$tdir/$fileName";
            $file_turl = "https://site.org/" . "$dir/$fileName";
            $file_1url = "https://go.short.com/" . $uniqueCode;

            $FileBtns = [[['text' => $e['DownloadBtnText'], 'url' => $file_1url]], [['text' => $e['DeleteBtnText'], 'callback_data' => 'delete*' . $file_id]]];
            return ['text' => "\n\nFile Name: <b>$fileName</b>\nFile ID: <b><a href='https://t.me/12345bot?start=$uniqueCode'>$uniqueCode</a></b>\nLink: <code>https://go.short.com/$uniqueCode</code>\nRename: <b>/rename_$uniqueCode</b>", 'buttons' => $FileBtns];
        }
    }
    
    return false;
    logToFile('Save function finished.');
}

function updateUserFiles($chatid, $delete = false)
{
    global $conn;

    $sql = "SELECT files FROM users WHERE chat_id ='$chatid' LIMIT 1";
    $q = $conn->query($sql);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $oldFileCount = $r["files"];
    if ($delete) {
        $newFileCount = $oldFileCount - 1;
    } else {
        $newFileCount = $oldFileCount + 1;
    }

    $Update = "UPDATE users SET files='$newFileCount' WHERE chat_id ='$chatid'";
    $ap = $conn->prepare($Update);
    $ap->execute();
}

function saveMessageIdFiles($fileID, $messageID)
{
    global $conn;

    $sql = "SELECT * FROM files WHERE file_id ='$fileID' LIMIT 1";
    $q = $conn->query($sql);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $oldMessageID = $r["message_id"];
    $newMessageID = "$messageID,$oldMessageID";
    if (empty($oldMessageID)) {
        $Update = "UPDATE files SET message_id='$messageID' WHERE file_id ='$fileID'";
    } else {
        $Update = "UPDATE files SET message_id='$newMessageID' WHERE file_id ='$fileID'";
    }
    $ap = $conn->prepare($Update);
    $ap->execute();
}

function editMessageFileDeleted($userID, $messageID)
{
    global $e;

    $message_id = explode(",", $messageID);
    $count = count($message_id);

    for ($i = 0; $i < $count; $i++) {
        Send("editMessageText", [
            "chat_id" => $userID,
            "message_id" => $message_id[$i],
            "parse_mode" => "HTML",
            "text" => $e["FileIsRemoved"],
        ]);
    }
}

// Функция для получения списка файлов пользователя из базы данных
function getFileList($chat_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM files WHERE user_id = :user_id");
    $stmt->execute([
        ":user_id" => $chat_id,
    ]);
    $fileList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $fileList;
}

function updateUserDetails($chat_id,$fullName,$username){
    global $conn;
    $Update="UPDATE users SET active = 1, full_name=:full_name, username=:username WHERE chat_id=:chat_id";
    $ap=$conn->prepare($Update);
    $ap->bindParam(":full_name",$fullName);
    $ap->bindParam(":username",$username);
    $ap->bindParam(":chat_id",$chat_id);
    $ap->execute();
    
}

// Функция для получения статистики по количеству пользователей и файлов
function getStats()
{
    global $conn;

    // Получение количества пользователей
    $stmtUsers = $conn->query("SELECT COUNT(*) as userCount FROM users");
    $rowUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC);
    $userCount = $rowUsers["userCount"];

    // Получение количества файлов
    $stmtFiles = $conn->query("SELECT COUNT(*) as fileCount FROM files");
    $rowFiles = $stmtFiles->fetch(PDO::FETCH_ASSOC);
    $fileCount = $rowFiles["fileCount"];

    return [
        "userCount" => $userCount,
        "fileCount" => $fileCount,
    ];
}

function getUserStats($user_id)
{
    global $conn;

    // Получение количества загруженных файлов для пользователя
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as fileCount FROM files WHERE user_id = :user_id"
    );
    $stmt->execute([
        ":user_id" => $user_id,
    ]);
    $fileCount = $stmt->fetch(PDO::FETCH_ASSOC)["fileCount"];

    return $fileCount;
}

function generateFileButtons($fileList, $currentPage, $filesPerPage = 20)
{
    $fileCount = count($fileList);
    $startIndex = ($currentPage - 1) * $filesPerPage;
    $endIndex = $startIndex + $filesPerPage;

    $buttons = [];
    for ($i = $startIndex; $i < $endIndex && $i < $fileCount; $i++) {
        $file = $fileList[$i];
        $fileId = $file["origin_file_id"];
        $fileName = $file["file_name"];

        $buttons[] = [["text" => $fileName, "callback_data" => "file_$fileId"]];
    }

    // Adding navigation buttons
    if ($fileCount > $filesPerPage) {
        $prevButton =
            $currentPage > 1
                ? ["text" => "◀️ Previous", "callback_data" => "prev_page"]
                : null;
        $nextButton =
            $endIndex < $fileCount
                ? ["text" => "Next ▶️", "callback_data" => "next_page"]
                : null;

        if ($prevButton && $nextButton) {
            $buttons[] = [$prevButton, $nextButton];
        } elseif ($prevButton) {
            $buttons[] = [$prevButton];
        } elseif ($nextButton) {
            $buttons[] = [$nextButton];
        }
    }

    return $buttons;
}

// Функция для разделения списка файлов на два ряда кнопок
function splitFilesIntoTwoRows($fileList)
{
    $buttons = [];
    $rowCount = 0;

    foreach ($fileList as $file) {
        $buttonIndex = floor($rowCount / 2);

        if (!isset($buttons[$buttonIndex])) {
            $buttons[$buttonIndex] = [];
        }

        // Создание кнопки для файла (замените 'text' и 'callback_data' на нужные значения)
        $fileButton = [
            "text" => $file["file_rename"], // Например, замените на название файла
            "callback_data" => "file_" . $file["origin_file_id"], // Идентификатор файла
        ];

        $buttons[$buttonIndex][] = $fileButton;
        $rowCount++;
    }

    return $buttons;
}

function getAllUserChatIDs()
{
    global $conn;

    $stmt = $conn->prepare("SELECT chat_id FROM users WHERE active = 1");
    $stmt->execute();
    $chatIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $chatIDs;
}

function getFreeUserChatIDs()
{
    global $conn;

    $stmt = $conn->prepare("SELECT chat_id FROM users WHERE active = 1 AND plan < 3");
    $stmt->execute();
    $chatIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $chatIDs;
}

function updateCommands($chat_id)
{
    $commands = [
        ["command" => "start", "description" => "❣️start/restart bot"],
        ["command" => "my_files", "description" => "❣️list of your files"],
        ["command" => "me", "description" => "❣️your profile"],
        ["command" => "help", "description" => "❣️help menu"],
        ["command" => "code", "description" => "❣️enter your promo"],
        ["command" => "paid", "description" => "❣️tariff plans"],
        ["command" => "my_id", "description" => "❣️your ID"],
        ["command" => "get", "description" => "❣️Get a file by FileID"],
        // Добавьте остальные команды с описаниями по мере необходимости
    ];

    // Отправка запроса к API Telegram для обновления списка команд бота
    $response = Send("setMyCommands", ["commands" => json_encode($commands)]);

    // Проверка успешности запроса и отправка сообщения с результатом
    if ($response && $response->ok) {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Список команд успешно обновлен!",
        ]);
        return true; // Обновление списка команд выполнено успешно
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Ошибка при обновлении списка команд.",
        ]);
        return false; // Обновление списка команд не удалось
    }
}

function firstInteraction($chat_id)
{
    global $conn;

    $checkUserQuery = $conn->prepare(
        "SELECT * FROM users WHERE chat_id = :chat_id"
    );
    $checkUserQuery->bindParam(":chat_id", $chat_id);
    $checkUserQuery->execute();
    $userExists = $checkUserQuery->fetch(PDO::FETCH_ASSOC);

    if (!$userExists) {
        // If user doesn't exist, insert their details with oneStart as the date
        $insertQuery = $conn->prepare(
            "INSERT INTO users (chat_id, oneStart, lang) VALUES (:chat_id, :oneStart, :lang)"
        );
        $insertQuery->bindParam(":chat_id", $chat_id);
        $oneStart = date("Y-m-d"); // Get current date and time
        $insertQuery->bindParam(":oneStart", $oneStart);
        $insertQuery->bindParam(":lang", $lang);
        $insertQuery->execute();
    }
}

function updateOldInteractionDate($chat_id)
{
    global $conn;

    $checkUserQuery = $conn->prepare(
        'SELECT * FROM users WHERE chat_id = :chat_id AND oneStart = "0000-00-00"'
    );
    $checkUserQuery->bindParam(":chat_id", $chat_id);
    $checkUserQuery->execute();
    $userToUpdate = $checkUserQuery->fetch(PDO::FETCH_ASSOC);

    if ($userToUpdate) {
        // If user exists and has '0000-00-00' as the interaction date, update it
        $updateQuery = $conn->prepare(
            "UPDATE users SET oneStart = :newDate WHERE chat_id = :chat_id"
        );
        $newDate = date("Y-m-d", strtotime("-2 months")); // Calculate date 2 months ago
        $updateQuery->bindParam(":newDate", $newDate);
        $updateQuery->bindParam(":chat_id", $chat_id);
        $updateQuery->execute();
    }
}

function setTariffPlan($chat_id, $plan_id, $duration)
{
    global $conn;

    try {
        if ($duration == -1) {
            $planEndDate = '0000-00-00';
            
            // Получаем информацию о тарифе из базы данных по его ID
            $getPlanQuery = $conn->prepare(
                "SELECT name FROM plans WHERE id=:plan_id"
            );
            $getPlanQuery->bindParam(":plan_id", $plan_id);
            $getPlanQuery->execute();
            $planData = $getPlanQuery->fetch(PDO::FETCH_ASSOC);
            
            if ($planData) {
                $planName = $planData["name"];
            }
        } else {
            // Получаем информацию о тарифе из базы данных по его ID
            $getPlanQuery = $conn->prepare(
                "SELECT name FROM plans WHERE id=:plan_id"
            );
            $getPlanQuery->bindParam(":plan_id", $plan_id);
            $getPlanQuery->execute();
            $planData = $getPlanQuery->fetch(PDO::FETCH_ASSOC);

            if ($planData) {
                $planName = $planData["name"];

                // Определяем дату окончания плана: сегодняшняя дата + указанная длительность
                $planEndDate = date("Y-m-d", strtotime("+$duration months")); // Можно использовать другие единицы времени, если нужно
            } else {
                // Notify if the plan ID wasn't found
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => "The plan ID was not found.",
                ]);
                return;
            }
        }

        // SQL запрос для обновления плана пользователя и даты окончания плана
        $updatePlanQuery = $conn->prepare(
            "UPDATE users SET plan=:plan_id, planEnd=:planEndDate WHERE chat_id=:chat_id"
        );
        $updatePlanQuery->bindParam(":plan_id", $plan_id);
        $updatePlanQuery->bindParam(":planEndDate", $planEndDate);
        $updatePlanQuery->bindParam(":chat_id", $chat_id);
        $updatePlanQuery->execute();

        if ($duration == -1) {
            // Notify the user about the plan update with an indefinite duration
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Your plan has been updated to $planName with an indefinite duration.",
            ]);
        } else {
            // Notify the user about the updated plan with the plan name and end date
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "Your plan has been updated to $planName. Your plan will be valid until $planEndDate.",
            ]);
        }
    } catch (PDOException $ex) {
        // Handle exceptions if any
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" =>
                "An error occurred while updating the plan: " .
                $ex->getMessage(),
        ]);
    }
}


function getUserRegistrationDate($chat_id)
{
    global $conn;

    $query = $conn->prepare(
        "SELECT oneStart FROM users WHERE chat_id = :chatID"
    );
    $query->bindParam(":chatID", $chat_id);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result["oneStart"])) {
        // Предполагается, что 'oneStart' содержит дату в формате 'Y-m-d'
        return strtotime($result["oneStart"]); // Возвращаем дату в формате Unix timestamp
    }

    return null; // Если не удалось получить дату регистрации
}

function processPromoCodes($chat_id, $promo) {
    global $conn; // Предполагается, что у вас есть активное PDO-соединение с базой данных

    // Проверяем, использовал ли уже промокод данный пользователь
    $checkUsageQuery = $conn->prepare("SELECT * FROM promo_usage WHERE promocode = :promo AND chat_id = :chat_id");
    $checkUsageQuery->bindParam(':promo', $promo);
    $checkUsageQuery->bindParam(':chat_id', $chat_id);
    $checkUsageQuery->execute();

    $usageData = $checkUsageQuery->fetch(PDO::FETCH_ASSOC);

    if ($usageData) {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "This promo code has already been used by you.",
        ]);
        return;
    }

    // Запрос к таблице codes для поиска промокода
    $query = "SELECT * FROM codes WHERE promocode = :promo";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':promo', $promo);
    $stmt->execute();
    $codeData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($codeData) {
        $currentDate = date("Y-m-d");
        $registerBeforeTime = $codeData['register_before_time'];
        $timeEnd = $codeData['time_end'];
        $limitation = $codeData['limitation'];
        
        // Получаем тариф пользователя
        $userPlan = getUserPlan($chat_id);

        if (($registerBeforeTime === '0000-00-00' || $currentDate < $registerBeforeTime) &&
            ($timeEnd === '0000-00-00' || $currentDate <= $timeEnd)) {

            if ($limitation !== -1 && $limitation <= 0) {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => "This promo code has reached its activation limit.",
                ]);
                return;
            }

            // Проверки пройдены, промокод работает
            $tariff = $codeData['tariff'];
            $longTime = $codeData['long_time'];

            if ($userPlan >= 0 && $tariff > $userPlan) {
                setTariffPlan($chat_id, $tariff, $longTime);
                
                // Уменьшаем оставшееся количество активаций, если ограничение не -1
                if ($limitation !== -1) {
                    $updateLimitQuery = $conn->prepare("UPDATE codes SET limitation = limitation - 1 WHERE promocode = :promo");
                    $updateLimitQuery->bindParam(':promo', $promo);
                    $updateLimitQuery->execute();
                }
                
                // Сохраняем использование промокода для данного пользователя
                $saveUsageQuery = $conn->prepare("INSERT INTO promo_usage (promocode, chat_id) VALUES (:promo, :chat_id)");
                $saveUsageQuery->bindParam(':promo', $promo);
                $saveUsageQuery->bindParam(':chat_id', $chat_id);
                $saveUsageQuery->execute();
            } else {
                Send("sendMessage", [
                    "chat_id" => $chat_id,
                    "text" => "Your current plan is already higher or equal to the promo code tariff.",
                ]);
            }
        } else {
            Send("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "You cannot use this promo code at the moment.",
            ]);
        }
    } else {
        Send("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "This promo code was not found.",
        ]);
    }
}

function renameFileByOriginFileId($origin_file_id, $newFileName)
{
    global $conn, $chat_id;

    // Получаем текущий план пользователя
    $userPlanQuery = $conn->prepare('SELECT plan FROM users WHERE chat_id = :chat_id');
    $userPlanQuery->execute(array(':chat_id' => $chat_id));
    $userPlanResult = $userPlanQuery->fetch(PDO::FETCH_ASSOC);

    // Проверяем, является ли пользователь владельцем файла или администратором
    $isOwnerOrAdmin = isFileOwner($origin_file_id, $chat_id) || isUserAdmin($chat_id);

    if ($userPlanResult && $userPlanResult['plan'] > 1 && $isOwnerOrAdmin) {
        // Получаем текущее имя файла из базы данных
        $stmt = $conn->prepare('SELECT file_name FROM files WHERE origin_file_id = :origin_file_id AND user_id = :user_id');
        $stmt->execute(array(':origin_file_id' => $origin_file_id, ':user_id' => $chat_id));
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            // Разделяем имя файла и расширение
            $currentFileName = $file['file_name'];
            $currentExtension = pathinfo($currentFileName, PATHINFO_EXTENSION);

            // Если у текущего файла было расширение, используем его в новом имени
            if (!empty($currentExtension)) {
                $newFileName = $newFileName . '.' . $currentExtension;
            }
            
            // Обновляем имя файла и расширение в базе данных
            $stmt = $conn->prepare('UPDATE files SET file_rename = :new_name WHERE origin_file_id = :origin_file_id AND user_id = :user_id');
            $stmt->execute(array(
                ':new_name' => $newFileName,
                ':origin_file_id' => $origin_file_id,
                ':user_id' => $chat_id
            ));

            $rowCount = $stmt->rowCount();

            return $rowCount > 0;
        } else {
            // Сообщаем пользователю, что файл не найден
            return "File not found.";
        }
    } else {
        // Сообщаем пользователю, что у него недостаточный тариф или прав
        return "You need a paid rate or proper permissions to rename files.";
    }

    return false;
}

function getPlanInfo($selectedPlan)
{
    global $conn; // Подключение к базе данных

    // Запрос к базе данных для получения информации о выбранном тарифе
    $stmt = $conn->prepare('SELECT * FROM plans WHERE id = :selectedPlan');
    $stmt->execute([':selectedPlan' => $selectedPlan]);
    $planInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    return $planInfo; // Возвращаем информацию о тарифе
}

function saveUserIDsToFile() {
    $userChatIDs = getAllUserChatIDs();
    $filename = 'user_ids.txt'; // Define the filename

    // Open or create the file
    $file = fopen($filename, 'w');

    // Write each user ID to the file
    foreach ($userChatIDs as $userID) {
        fwrite($file, $userID . "\n");
    }

    // Close the file
    fclose($file);
}

function markUserAsInactive($userID)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE users SET active = 0 WHERE chat_id = :userID");
    $stmt->bindParam(':userID', $userID);
    $stmt->execute();
}