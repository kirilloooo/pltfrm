<?php

#===============================================================================
#-------------------------------- Settings -------------------------------------
#===============================================================================

/*  Database */
define('HOST', 'localhost'); # Database host name
define('DBNAME', ''); # Database name
define('DBUSERNAME', ''); # Database username
define('DBPASSWORD', ''); # Database password

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

/*  Telegram Bot API Key */
define('API_KEY', ''); # Enter bot api token
define('APP_URL','https://api.telegram.org/bot'.API_KEY);   ## Don't edit this line ##

#===============================================================================
#-------------------------- Connect to database --------------------------------
#===============================================================================
try {
    $conn = new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8mb4', DBUSERNAME, DBPASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Include table definitions
    include_once __DIR__ . '/tables.php';

    // Execute SQL queries to create tables
    $conn->exec($codesTable); // Create 'codes' table
    $conn->exec($filesTable); // Create 'files' table
    $conn->exec($plansTable); // Create 'plans' table
    $conn->exec($promoUsageTable); // Create 'promo_usage' table
    $conn->exec($usersTable); // Create 'users' table
    
} catch(PDOException $e) {
    // Handle exceptions here
    // file_put_contents('Error_log.txt', $e->getMessage()); // Save Errors in: 'Error_log.txt'
}


#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

/*  Folders */
$tfolder = 'site.org';
$folder         =   'upload';   # Save All Files In Folder Name
$imageFolder    =   'image';    # Save Images In Folder Name
$videoFolder    =   'video';    # Save Videos In Folder Name
$musicFolder    =   'music';    # Save Musics In Folder Name
$fileFolder     =   'file';     # Save Other Files In Folder Name

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

// Получаем информацию о пользователе и его тарифном плане
$stmt = $conn->prepare("SELECT users.*, plans.delete_time FROM users INNER JOIN plans ON users.plan = plans.id WHERE users.chat_id = :chat_id");
$stmt->bindParam(':chat_id', $chat_id);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем, что данные пользователя получены успешно
if ($userData) {
    // Используем значение delete_time из таблицы plans для расчета $dayto
    $deleteTime = $userData['delete_time'];
    $dayto = $deleteTime / 86400;
    $dayto = round($dayto, 2);
    
    // Теперь у вас есть $dayto, основанный на тарифном плане пользователя
} else {
    // Обработка случая, если данные пользователя не были найдены
}

#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

/* lang */
$langs = 'en';

/*version*/
$version = "v0.8.182-b03012024-winter";

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

include_once __DIR__ . "/langs/unix.php";
include_once __DIR__ . "/langs/$langs.php";