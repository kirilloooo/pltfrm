<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('HOST', 'localhost');     # Database host name
define('DBNAME', '');   # Database name
define('DBUSERNAME', '');   # Database username
define('DBPASSWORD', '');   # Database password

function recursiveCopy($source, $dest) {
    if (is_dir($source)) {
        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                recursiveCopy("$source/$file", "$dest/$file");
            }
        }
    } else {
        copy($source, $dest);
    }
}

// Путь к папке, в которой будут сохранены резервные копии
$backupPath = '/home/.../backups/bots/FileWithURLBot/';

// Создаем имя для резервной копии на основе текущей даты и времени
$backupFileName = 'backup-' . date('Ymd-Hi') . '.zip';

// Создаем папку для файлов и базы данных
$backupFolderPath = $backupPath . $backupFileName . '/';

try {
    // Создаем подключение к базе данных
    $conn = new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8mb4', DBUSERNAME, DBPASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создаем папку для файлов
    if (!file_exists($backupFolderPath . 'files')) {
        mkdir($backupFolderPath . 'files', 0755, true);
    }

    // Создаем резервную копию файлов
    // Создаем папку для файлов
    if (!file_exists($backupFolderPath . 'files')) {
        mkdir($backupFolderPath . 'files', 0755, true);
    }
    
    // Копируем файлы из корневого каталога
    $sourceFiles = glob(__DIR__ . '/*'); // Получаем список файлов в корневом каталоге
    foreach ($sourceFiles as $sourceFile) {
        // Исключаем себя (backup-cron.php) из копирования
        if (basename($sourceFile) !== basename(__FILE__)) {
            $destinationFile = $backupFolderPath . 'files/' . basename($sourceFile);
            if (is_dir($sourceFile)) {
                recursiveCopy($sourceFile, $destinationFile);
            } else {
                copy($sourceFile, $destinationFile);
            }
        }
    }
    
    // Создаем резервную копию базы данных
    $dumpCommand = 'mysqldump -h'.HOST.' -u'.DBUSERNAME.' -p'.DBPASSWORD.' '.DBNAME.' > '.$backupFolderPath.'database.sql';
    exec($dumpCommand);

    // Создаем zip-архив с использованием абсолютного пути
    $zipCommand = 'zip -r '.$backupPath.$backupFileName.'.zip . -x ' . $backupFileName . '.zip';
    exec($zipCommand, $output, $zipResult);
    
    if ($zipResult !== 0) {
        // Если архивирование не удалось, выводим сообщение об ошибке и информацию о выполнении команды zip
        echo 'Ошибка при создании zip-архива. Результат выполнения команды zip: ' . implode("\n", $output);
    } else {
        // Удаляем временную папку
        $removeCommand = 'rm -rf '.$backupFolderPath;
        exec($removeCommand);
    
        // Отображаем успешное сообщение
        echo 'Резервная копия успешно создана.';
    }

} catch(PDOException $e) {
    // Обработка исключений
    $errorMessage = $e->getMessage();
    file_put_contents($backupPath.'error_log.txt', $errorMessage);
    echo 'Ошибка при создании резервной копии. Подробности: ' . $errorMessage;
}
?>
