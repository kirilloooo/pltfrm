<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../includes/settings.php';

header('Content-Type: application/json');

// Define an array of valid API keys
$validApiKeys = ['zkyrylo', 'tdrive', 'open-api'];

// Check if the 'apikey' parameter is provided
$apiKey = isset($_GET['key']) ? $_GET['key'] : '';

// Check if the provided API key is valid
if (!in_array($apiKey, $validApiKeys)) {http_response_code(403); // Forbiddenecho json_encode(['error' => 'Invalid API key']);exit();
}

$result = [];

$result['name'] = 'PLTFRM';

// Get bot description (you can replace this with your own description)
$result['description'] = 'A handy tool for fast and secure file transfer in Telegram. Simply upload a file up to 20MB in size and the bot will provide you with a temporary link to the file. It\'s perfect for quickly sharing large files without having to send them directly to chat.';

// Get bot version
$result['version'] = $version;

$result['file-lifetime'] = ['days' => $dayto, 'seconds' => $FilesDeleteAfter];

$result['website'] = 'https://site.com/';
$result['botURL'] = 'https://t.me/123456bot';

if (isset($_GET['stats']) && $_GET['stats'] === 'yes') {// Получение статистики$stats = getStats();$result['stats'] = [    'userCount' => $stats['userCount'],    'fileCount' => $stats['fileCount']];
}

if (isset($_GET['user_id'])) {$userId = $_GET['user_id'];
$userInfoQuery = $conn->prepare('SELECT * FROM users WHERE chat_id = :chat_id');$userInfoQuery->bindParam(':chat_id', $userId);$userInfoQuery->execute();$userInfo = $userInfoQuery->fetch(PDO::FETCH_ASSOC);
if ($userInfo) {    $userPlan = $userInfo['plan'];
    $userPlanInfoQuery = $conn->prepare('SELECT * FROM plans WHERE id = :plan_id');    $userPlanInfoQuery->bindParam(':plan_id', $userPlan);    $userPlanInfoQuery->execute();    $planInfo = $userPlanInfoQuery->fetch(PDO::FETCH_ASSOC);
    $userFilesQuery = $conn->prepare('SELECT COUNT(*) as fileCount FROM files WHERE user_id = :user_id');    $userFilesQuery->bindParam(':user_id', $userId);    $userFilesQuery->execute();    $userFiles = $userFilesQuery->fetch(PDO::FETCH_ASSOC);        if ($userInfo['ban'] == 0) {        $bantext = 'noban';    } else if ($userInfo['ban'] == 1) {        $bantext = 'ban';    } else {        $bantext = 'error';    }        if ($userInfo['planEnd'] == "0000-00-00") {        $plan_text = 'unlimited';    } else {        $plan_text = $userInfo['planEnd'];    }        if ($userInfo['username'] == null) {        $username_text = 'no set';    } else {        $username_text = "https://t.me/" . $userInfo['username'];    }        $timestamp = date("Y-m-d", $userInfo['date']);
    $result['user'] = [        'user_id' => $userId,        'username' => $username_text,        'full_name' => $userInfo['full_name'],        'file_count' => $userFiles['fileCount'],        'plan_end' => $plan_text,        'plan_file_count' => $planInfo['countFiles'],        'plan_name' => $planInfo['name'],        'first_start' => $userInfo['oneStart'],        'last_interaction' => $timestamp,        'date_format' => "Y-m-d",        'status' => [            'ban' => $userInfo['ban'],            'hint' => $bantext],    ];}
}

if (isset($_GET['plans']) && $_GET['plans'] === 'yes') {$result = [];
// Добавляем информацию о ссылке$result['write_to_pay'] = 'https://t.me/123456';
// Получаем информацию о тарифах$plansQuery = $conn->query('SELECT * FROM plans');$plans = $plansQuery->fetchAll(PDO::FETCH_ASSOC);
$result['plans'] = [];
foreach ($plans as $plan) {    $result['plans'][] = [        'plan_name' => $plan['name'],        'plan_description' => $plan['description'],        'plan_file_count' => $plan['countFiles'],    ];}
}


echo json_encode($result, JSON_PRETTY_PRINT);
