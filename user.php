<?php

// Include the database connection settings
include_once __DIR__ . '/../includes/settings.php';


// Get the user ID from the URL parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {$user_id = $_GET['id'];
} else {die("Invalid user ID.");
}

// Function to get the user's files from the database
function getUserFiles($user_id)
{global $conn;
$stmt = $conn->prepare('SELECT * FROM files WHERE user_id = :user_id');$stmt->execute(array(':user_id' => $user_id));$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
return $files;
}

// Function to calculate remaining time before file deletion
function timeUntilDeletion($uploadDate)
{$uploadDateTime = new DateTime($uploadDate);$fourDaysInterval = new DateInterval('P4D'); // Interval for 4 days
$expiryDate = clone $uploadDateTime;$expiryDate->add($fourDaysInterval); // Calculate the expiry date
$currentDateTime = new DateTime(); // Current date and time
// Calculate the interval between current time and expiry time$timeRemaining = $currentDateTime->diff($expiryDate);
return $timeRemaining;
}

// Get the user's files
$user_files = getUserFiles($user_id);

?>

<!DOCTYPE html>
<html>

<head><title>User Files</title><link rel="icon" type="image/png" href="./favicon.ico"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<body><div class="container mt-4">    <h1 class="text-uppercase">User Files</h1>    <table class="table table-striped table-bordered mt-3">        <thead>            <tr>                <th scope="col">№</th>                <th scope="col">FileID</th>                <th scope="col">File Size</th>                <th scope="col">Download Link</th>                <th scope="col">Pre-deletion</th>            </tr>        </thead>        <tbody>            <?php foreach ($user_files as $file): ?>                <tr>                    <td>                        <?php echo $file['id']; ?>                    </td>                    <td>                        <?php echo $file['file_name']; ?>                    </td>                    <td>                        <?php                        $fileSizeInBits = $file['file_size'];
                        if ($fileSizeInBits < 1048576) {                            $fileSizeInKilobytes = $fileSizeInBits / 1024; // 1,024 bits = 1 KB                            $fileSizeInKilobytesRounded = round($fileSizeInKilobytes, 2); // Rounded to two decimal places                                                if ($fileSizeInKilobytes < 1) {                                echo $fileSizeInBits . " bytes";                            } else {                                echo $fileSizeInKilobytesRounded . " KB";                            }                        } else {                            $fileSizeInMegabytes = $fileSizeInBits / 1048576; // 1,048,576 bits = 1 MB                            $fileSizeInMegabytesRounded = round($fileSizeInMegabytes, 2); // Rounded to two decimal places                                                echo $fileSizeInMegabytesRounded . " MB";                        }                        ?>                    </td>                    <td><a href="/file?id=<?php echo $file['file_id']; ?>"                            target="_blank">Download</a></td><td>
<?php
// Получить текущую временную метку
$current_time = time();

// Длительность хранения файла (в секундах) - 7 дня
$file_retention_period = $dayto * 24 * 60 * 60; // 7 дня * 24 часа * 60 минут * 60 секунд

foreach ($user_files as $file) {$file_upload_time = $file['date']; // Временная метка загрузки файла$time_difference = $current_time - $file_upload_time;// Рассчитать оставшееся время до удаления файла$time_remaining = $file_retention_period - $time_difference;if ($time_remaining > 0) {    // Преобразовать оставшееся время в дни, часы, минуты и секунды    $days_remaining = floor($time_remaining / (24 * 60 * 60));    $time_remaining %= (24 * 60 * 60);    $hours_remaining = floor($time_remaining / (60 * 60));        echo "{$days_remaining} day(s), {$hours_remaining} hour(s).";        // Прервать цикл после вывода оставшегося времени один раз    break;}
}
?>
</td>
                </tr>            <?php endforeach; ?>        </tbody>    </table></div>    <!-- Footer section --><footer class="footer mt-auto py-2 bg-light text-center">    <div class="container">        <div class="my-2">            <a href="https://site.com"><button class="btn btn-secondary btn-sm mx-1">Home</button></a>            <a href="https://site.com/stats"><button class="btn btn-secondary btn-sm mx-1">Stats</button></a>            <a href="https://site.com/doc"><button class="btn btn-secondary btn-sm mx-1">API</button></a>        </div>        <span class="text-muted">© 2023 PLTFRM. All rights reserved. Version: <?php echo $version; ?></span>    </div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>