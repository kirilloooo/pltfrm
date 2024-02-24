<?php
$token = '';
$chat_id = '-100123456';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$message = $_POST['message17'] ?? '';
if (!empty($message)) {    $telegram_url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);    $result = file_get_contents($telegram_url);
    if ($result) {        echo 'Message successfully sent.';    } else {        echo 'Error when sending a message.';    }} else {    echo 'The message to be sent is blank.';}
} else {echo 'The request method is not supported.';
}
?>
