<?php
$mainDirs = ['file', 'video', 'image', 'music'];
$uploadsDir = 'upload/';

// Проверяем каждую основную папку
foreach ($mainDirs as $mainDir) {$dir = $uploadsDir . $mainDir . '/';// Проверяем, существует ли указанная директорияif (is_dir($dir)) {    // Получаем список всех папок в указанной директории    $folders = glob($dir . '*', GLOB_ONLYDIR);
    // Проходим по каждой папке в основной папке    foreach ($folders as $folder) {        // Проверяем, пустая ли папка        if (count(glob($folder . '/*')) === 0) {            // Если папка пустая, удаляем её            rmdir($folder);            echo "Папка $folder удалена, так как она была пустой.<br>";        }    }} else {    echo "Указанная директория '$mainDir' не существует.<br>";}
}
?>

