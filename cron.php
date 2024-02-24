<?php
include_once __DIR__ . '/includes/settings.php';
include_once __DIR__ . '/includes/functions.php';

#===============================================================================
#------------------------------ Check Files ------------------------------------
#===============================================================================
function deleteUserFilesCount($userID)
{
    global $conn;

    $sql = "SELECT files FROM users WHERE chat_id ='$userID' LIMIT 1";
    $q = $conn->query($sql);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $oldFileCount = $r['files'];
    $newFileCount = $oldFileCount - 1;
    $Update = "UPDATE users SET files='$newFileCount' WHERE chat_id ='$userID'";
    $ap = $conn->prepare($Update);
    $ap->execute();
}

function checkAndUpdateTariffDate($userID)
{
    global $conn;

    $getUserQuery = $conn->prepare("SELECT * FROM users WHERE chat_id=:userID");
    $getUserQuery->bindParam(':userID', $userID);
    $getUserQuery->execute();
    $userData = $getUserQuery->fetch(PDO::FETCH_ASSOC);

    $currentDate = time();
    $tariffEndDate = strtotime($userData['planEnd']);

    // Пропускаем пользователей с тарифом без ограничения по дате
    if ($userData['planEnd'] === '0000-00-00')
    {
        return;
    }

    if ($tariffEndDate !== false && $currentDate >= $tariffEndDate)
    {
        // Тарифное время истекло, обновляем тариф и дату окончания
        $updateTariffQuery = $conn->prepare("UPDATE users SET plan='1', planEnd='0000-00-00' WHERE chat_id=:userID");
        $updateTariffQuery->bindValue(':userID', $userID);
        $updateTariffQuery->execute();

        // Уведомление пользователя о истечении тарифа
        Send('sendMessage', ['chat_id' => $userID, 'text' => "Your plan has expired. Please renew your tariff.", ]);
    }
}

function deletePromoCodeAndUsage($promocode)
{
    global $conn;

    // Delete entries related to this promocode from promo_usage table
    $deleteUsage = $conn->prepare("DELETE FROM promo_usage WHERE promocode = :promocode");
    $deleteUsage->bindParam(':promocode', $promocode);
    $deleteUsage->execute();

    // Delete the promocode from the codes table
    $deletePromocode = $conn->prepare("DELETE FROM codes WHERE promocode = :promocode");
    $deletePromocode->bindParam(':promocode', $promocode);
    $deletePromocode->execute();
}

function checkAndUpdateUserActivity($userID)
{
    global $conn;

    $getUserQuery = $conn->prepare("SELECT * FROM users WHERE chat_id=:userID, active=1");
    $getUserQuery->bindParam(':userID', $userID);
    $getUserQuery->execute();
    $userData = $getUserQuery->fetch(PDO::FETCH_ASSOC);

    $currentDate = time();
    $lastInteractionDate = strtotime($userData['date']);

    // Check if the user has not interacted for more than a week
    if (($currentDate - $lastInteractionDate) > (7 * 24 * 60 * 60)) {
        // Set the user as inactive
        $updateInactiveQuery = $conn->prepare("UPDATE users SET active=0 WHERE chat_id=:userID");
        $updateInactiveQuery->bindValue(':userID', $userID);
        $updateInactiveQuery->execute();

        // Optionally, you can notify the user about becoming inactive
        Send('sendMessage', ['chat_id' => $userID, 'text' => "You have been marked as inactive due to inactivity. Use /start to activate your account."]);

        return true; // User marked as inactive
    }

    return false; // User is still active
}


$usersQuery = $conn->query('SELECT chat_id FROM users');
while ($userData = $usersQuery->fetch(PDO::FETCH_ASSOC)) {
    checkAndUpdateUserActivity($userData['chat_id']);
}

// Check and delete promocodes with zero uses
$zeroLimitQuery = $conn->query("SELECT promocode FROM codes WHERE limitation = 0");
while ($row = $zeroLimitQuery->fetch(PDO::FETCH_ASSOC)) {
    $promocode = $row['promocode'];
    deletePromoCodeAndUsage($promocode);
}

// Check and delete expired promocodes
$expiredQuery = $conn->query("SELECT promocode FROM codes WHERE time_end != '0000-00-00' AND time_end < CURDATE()");
while ($row = $expiredQuery->fetch(PDO::FETCH_ASSOC)) {
    $promocode = $row['promocode'];
    deletePromoCodeAndUsage($promocode);
}

$sqlCron = 'SELECT * FROM files';
$qCron = $conn->query($sqlCron);
$rCron = $qCron->setFetchMode(PDO::FETCH_ASSOC);
while ($rCron = $qCron->fetch()) {
    $fileDate = $rCron['date']; // Assuming this represents the file's creation date
    $userID = $rCron['user_id'];
    $filePlan = $rCron['owner_plan']; // Assuming owner_plan corresponds to the user's tariff plan

    // Fetch the delete_time from the user's plan
    $getPlanQuery = $conn->prepare("SELECT delete_time FROM plans WHERE id=:filePlan");
    $getPlanQuery->bindParam(':filePlan', $filePlan);
    $getPlanQuery->execute();
    $planData = $getPlanQuery->fetch(PDO::FETCH_ASSOC);

    if ($planData) {
        // Calculate deletion time based on the plan's delete_time
        $deleteAfter = $planData['delete_time'];
        $allowTime = $fileDate + $deleteAfter;

        if (time() >= $allowTime) {
            $fileID = $rCron['id'];
            $fileDir = $rCron['file_dir'];
            $fileName = $rCron['file_name'];
            $Delete = "DELETE FROM files WHERE id=:fileID"; // Use prepared statement

            $deleteFilesMessageID .= $rCron['message_id'] . ',';
            
            $fileDirPath = "$tfolder/$fileDir"; // Path to the file's folder
            $filePath = "$fileDirPath/$fileName"; // Full path to the file

            unlink($filePath); // Delete the file

            // Delete the folder if it's empty
            if (is_dir($fileDirPath) && count(glob("$fileDirPath/*")) === 0) {
                rmdir($fileDirPath); // Delete the folder if it's empty
            }
            
            $deleteFileQuery = $conn->prepare($Delete);
            $deleteFileQuery->bindParam(':fileID', $fileID);
            $deleteFileQuery->execute(); // Delete file in the database

            deleteUserFilesCount($userID); // Update user files count
        }
    }
}


// После цикла проверяем и обновляем тарифный план пользователя
$usersQuery = $conn->query('SELECT chat_id FROM users');
while ($userData = $usersQuery->fetch(PDO::FETCH_ASSOC))
{
    checkAndUpdateTariffDate($userData['chat_id']);
}

$DELETEFILES = true;
include_once __DIR__ . '/bot.php'; # Edit user message