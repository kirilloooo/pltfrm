<!DOCTYPE html>
<html>
<head><title>Просмотр лога ошибок</title><meta http-equiv="refresh" content="5"> <!-- Обновление страницы каждые 5 секунд --><style>    body {        font-family: Arial, sans-serif;    }    pre {        white-space: pre-wrap;        word-wrap: break-word;    }</style>
</head>
<body><h1>Просмотр лога ошибок</h1><pre>    <?php    // Путь к файлу лога ошибок    $logFilePath = 'http://bot.site.com/123456Bot/error.log';
    // Чтение содержимого файла    $logContent = @file_get_contents($logFilePath);
    // Вывод содержимого файла (или сообщение об ошибке, если файл не удалось прочитать)    if ($logContent === false) {        echo "Не удалось загрузить лог ошибок.";    } else {        echo htmlspecialchars($logContent); // Вывод содержимого с экранированием HTML-тегов    }    ?></pre>
</body>
</html>
