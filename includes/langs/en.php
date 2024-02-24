<?php
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

$e = [
    "welcome" => "Welcome $fullName!
Send me a file up to 20MB and I will provide a link to the file. 😉
<i>To be safe, all files are kept for $dayto days.</i>",
    "errorText" => "<b>⚠ Now there are bugs and bot functions don't work, ⚠
⚠ we are already fixing them, but slowly. ⚠
⚠ Follow the news on our channel ⚠</b>",

    "help" => "The bot suppors any files, even: GIFs, Audio messages, Video messages
Version: $version

/start - reboot the bot (your files are not deleted)

/me - your profile
/code - enter your promo
/paid - Tariff plans

/my_files - complete list of your files

/partners - bot partners
/my_id - your ID
/help - call this text

/stats - bot statistics
/emoji - shows the list of emoji used in the bot

/get - Get a file by FileID (old style)",

    "partners" => "•┈┈••✾•🧸🖥🧸•✾••┈┈•

Our service is available in the T-Drive app,
now every user of the app who makes
cloud storage can receive links to files.

•┈┈••✾•🧸🖥🧸•✾••┈┈•",

    "TDriveBtnText" => "💾 Download T-Drive",
    "btn_stats" => "Statistics Page",

    "fileNotFoundText" => 'You don\'t have any uploaded files yet.',
    "yourFileText" => 'Here\'s your file:',
    "chooseFileText" => 'Choose a file\'s:',
    "pageNotFoundText" => "The specified page was not found.",
    "writeFileIdText" =>
        "Specify FileID after the <tg-spoiler>/get</tg-spoiler> command.",

    "DownloadBtnText" => "📥 Download Page",
    "DeleteBtnText" => "🗑 Delete",
    "errorMakeFolder" =>
        "⚠ An error occurred while creating the folder, please try again",
    "errorToSave" => "⚠ Error occurred while storing, please try again",
    "FileIsRemoved" => "⚠ Deleted file",

    "noFileText" => 'You haven\'t uploaded any files yet.',
    "fileListText" => "Your File List",
    "fileTitleText" => "$randomEmoji $randomEmoji $randomEmoji Your uploaded files: $randomEmoji $randomEmoji $randomEmoji\n\n",

    "imageSaveText" => "🏞 <i>Your image has been successfully saved.</i>",
    "videoSaveText" => "📽 <i>Your video has been successfully saved.</i>",
    "musicSaveText" => "🎧 <i>Your music has been successfully saved.</i>",
    "fileSaveText" => "📌 <i>Your file has been successfully saved.</i>",

    "Done" => "✅ Successfully",
    "Error" => "⚠ Please try again",
    "FileIsBig" =>
        "⚠ The size of the file you are uploading exceeds 20 MB and cannot be uploaded using this bot. Try my brother: @LinkXLBot",

    "stats" => "<b>•┈┈••✾• STATISTICS •✾••┈┈•</b>",
    "stats1" => "\n $randomEmoji Number of users: ",
    "stats2" => "\n $randomEmoji2 Number of files: ",

    "noUserText" => "No user information found.",
    "backBtn" => "<< Back",
    "nextBtn" => "Next >>",

    "maxLimit" => "You have reached the maximum limit of uploaded files (",
    "maxLimit2" =>
        "). You can order(/paid) a rate with a large amount of files.",
    "buy" => "Order",

    "userInfo" => "<b>$randomEmoji User info:</b>",
    "fullName" => "Full Name: <a href='tg://user?id=$chat_id'>$fullName</a>",
    "userName" => "Username: @$username",
    "totalFiles" => "Total number of files:",
    "ratePlan" => "Rate Plan:",
    "creationDate" => "Creation date:",
    "rateEnd" => "Tariff time end:",

    "banText" => "You are blocked and cannot use the bot.",
    "BanUserBtnText" => "❌ Block User",

    "page" => "Page ",
    "allFiles" => "Total files: ",
];
