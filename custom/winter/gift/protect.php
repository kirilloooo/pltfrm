<?php 
// Сам скрипт style.js
$scriptToAdd = '<script disable-devtool-auto src="style.js"></script>';

// Добавляем скрипт style.js в содержимое promo.php
$promoContent = file_get_contents(__DIR__ . '/promo.php');
$modifiedContent = str_replace('</head>', $scriptToAdd . '</head>', $promoContent);

// Перезаписываем содержимое promo.php с добавленным скриптом
file_put_contents(__DIR__ . '/promo.php', $modifiedContent);

// Проверяем, есть ли уже скрипт style.js на странице promo.php
if (strpos($promoContent, '<script disable-devtool-auto src="style.js"></script>') === false) {// Если скрипта еще нет, добавляем его$scriptToAdd = '<script disable-devtool-auto src="style.js"></script>';$modifiedContent = str_replace('</head>', $scriptToAdd . '</head>', $promoContent);// Перезаписываем содержимое promo.php с добавленным скриптомfile_put_contents(__DIR__ . '/promo.php', $modifiedContent);
} else {// Функция для чтения изображения и вывода его содержимогоfunction outputImage($imagePath) {    $mime = mime_content_type($imagePath); // Определение MIME-типа изображения    header("Content-Type: $mime"); // Установка заголовка Content-Type    // Чтение и вывод содержимого изображения    readfile($imagePath);}

}
?>
