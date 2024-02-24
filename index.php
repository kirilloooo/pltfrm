<?php
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

$imageTypes = [
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png" => "image/png",
    "gif" => "image/gif",
    "svg" => "image/svg+xml",
    "webp" => "image/webp",
    "avif" => "image/avif",
    "apng" => "image/apng",
    "ico" => "image/vnd.microsoft.icon",
    "bmp" => "image/bmp",
    "tiff" => "image/tiff",
    "tif" => "image/tiff",
    // Добавьте другие форматы изображений и их типы по необходимости
];

$fileTypes = [
    "doc" => "application/msword",
    "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "pdf" => "application/pdf",
    "txt" => "text/plain",
    "rtf" => "application/rtf",
    "html" => "text/html",
    "xls" => "application/vnd.ms-excel",
    "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "ppt" => "application/vnd.ms-powerpoint",
    "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
    "zip" => "application/zip",
    // Добавьте другие форматы файлов и их типы по необходимости
];

$short_link = isset($_GET['short_link']) ? $_GET['short_link'] : '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($short_link)) {
        $stmt = $conn->prepare("SELECT * FROM files WHERE origin_file_id = :short_link");
        $stmt->bindParam(':short_link', $short_link);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Assuming you have the $file['user_id'] available
        $user_id = $file['user_id'];
        // Fetch user's plan details from the database
        $stmt = $conn->prepare('SELECT delete_time FROM plans WHERE id IN (SELECT owner_plan FROM files WHERE origin_file_id = :short_link)');
        $stmt->execute(array(':short_link' => $short_link));
        $user_plan = $stmt->fetch(PDO::FETCH_ASSOC);
        // Check if the plan details were fetched successfully
        if ($user_plan) {
            $delete_time = $user_plan['delete_time'];
            // Calculate the deletion date based on the user's plan
            $deletionDate = $file['date'] + $delete_time;
            $tmdlt = $delete_time / 86400;
            // Now, use $deletionDate in your JavaScript to update the timer
        } else {
            // Handle the case where the user's plan details were not found
        }

        if ($result) {
            $file_id = $result['file_id'];
            $owner_plan = $result['owner_plan'];

            if ($owner_plan < 2) {
                echo "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta name='viewport' content='width=device-width, initial-scale=1'>
                    <title>Redirect Tool</title>
                    <meta property='og:site_name' content='PLTFRM'>
                    <meta property='og:title' content='Download file". $file['file_rename']."'>";
                    
                        $fileType = pathinfo(strtolower($result["file_name"]), PATHINFO_EXTENSION);
    
                        if (array_key_exists($fileType, $imageTypes)) {
                            $imageType = $imageTypes[$fileType];
                            
                            $urlimgpath = 'https://site.com/' . $result['file_dir'] . '/' . $result['file_name'];
                            
                            // Создание нового объекта Imagick
                            $image = new Imagick($urlimgpath);
                            
                            // Получение ширины и высоты изображения
                            $width = $image->getImageWidth();
                            $height = $image->getImageHeight();
                            echo '<!-- Open Graph for images -->
                            <meta property="og:type" content="image">
                            <meta property="og:image" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">
                            <meta property="og:image:secure_url" content="https://site.com/download.php?id='.$result["file_id"].'">
                            <meta property="og:image:width" content="'.$width.'">
                            <meta property="og:image:height" content="'.$height.'">
                            <meta property="og:image:type" content="' . $imageType . '"> <!-- Тип изображения -->';
                            
                            echo '  <!-- Twitter Meta Tags -->
                              <meta name="twitter:card" content="summary_large_image">
                              <meta property="twitter:domain" content="go.site.com">
                              <meta property="twitter:url" content="https://go.site.com/'.$short_link.'">
                              <meta name="twitter:title" content="Download file ' . $result['file_rename'] . '">
                              <meta name="twitter:description" content="Download the '.$result['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">
                              <meta name="twitter:image" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">';
                        } elseif (array_key_exists($fileType, $fileTypes)) {
                            $fileType = $fileTypes[$fileType];
                            echo '<!-- Open Graph for files -->
                            <meta property="og:type" content="object">
                            <meta property="og:url" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">
                            <meta property="og:title" content="Download file ' . $result['file_rename'] . '">
                            <meta property="og:description" content="Download the '.$result['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">;
                            <meta property="og:locale" content="en_US">';
                        }
                    
                    echo "<style>
                        body, html {
                            margin: 0;
                            padding: 0;
                            height: 100%;
                            overflow: hidden;
                        }
                        #countdown {
                            position: absolute;
                            top: 10px;
                            left: 50%;
                            transform: translateX(-50%);
                            font-size: 20px;
                            background-color: #f0f0f0;
                            padding: 5px 10px;
                            border-radius: 5px;
                        }
                        #frame {
                            width: 100%;
                            height: calc(100%);
                            border: none;
                        }
                    </style>
                </head>
                <body>
                    <div id=\"countdown\">You will be redirected in 15 seconds.</div>
                    <iframe id=\"frame\" src=\"//site.com/ads\" frameborder=\"0\" sandbox></iframe>
                    <script>
                        var countdown = 15;
                        var countdownDisplay = document.getElementById('countdown');
                        var countdownInterval = setInterval(function() {
                            countdown--;
                            countdownDisplay.innerHTML = 'You will be redirected in ' + countdown + ' seconds.';
                            if (countdown <= 0) {
                                clearInterval(countdownInterval);
                                var redirectURL = 'https://site.com/file?id=' + encodeURIComponent('$file_id');
                                window.location.href = redirectURL;
                            }
                        }, 1000);
                    </script>
                </body>
                </html>
                ";
                exit();
            } else {
                // Если ссылка отсутствует или она из таблицы files, перенаправляем на ссылку из files
                $redirect_to = isset($file_id) ? 'https://site.com/file?id=' . urlencode($file_id) : '';
                header("Location: $redirect_to");
                                echo "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Redirect Tool</title>
                    <meta property='og:site_name' content='PLTFRM'>
                    <meta property='og:title' content='Download file". $file['file_rename']."'>";
                    
                        $fileType = pathinfo(strtolower($result["file_name"]), PATHINFO_EXTENSION);
    
                        if (array_key_exists($fileType, $imageTypes)) {
                            $imageType = $imageTypes[$fileType];
                            
                            $urlimgpath = 'https://site.com/' . $result['file_dir'] . '/' . $result['file_name'];
                            
                            // Создание нового объекта Imagick
                            $image = new Imagick($urlimgpath);
                            
                            // Получение ширины и высоты изображения
                            $width = $image->getImageWidth();
                            $height = $image->getImageHeight();
                            echo '<!-- Open Graph for images -->
                            <meta property="og:type" content="image">
                            <meta property="og:image" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">
                            <meta property="og:image:secure_url" content="https://site.com/download.php?id='.$result["file_id"].'">
                            <meta property="og:image:width" content="'.$width.'">
                            <meta property="og:image:height" content="'.$height.'">
                            <meta property="og:image:type" content="' . $imageType . '"> <!-- Тип изображения -->';
                            
                            echo '  <!-- Twitter Meta Tags -->
                              <meta name="twitter:card" content="summary_large_image">
                              <meta property="twitter:domain" content="go.site.com">
                              <meta property="twitter:url" content="https://go.site.com/'.$short_link.'">
                              <meta name="twitter:title" content="Download file ' . $result['file_rename'] . '">
                              <meta name="twitter:description" content="Download the '.$result['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">
                              <meta name="twitter:image" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">';
                        } elseif (array_key_exists($fileType, $fileTypes)) {
                            $fileType = $fileTypes[$fileType];
                            echo '<!-- Open Graph for files -->
                            <meta property="og:type" content="object">
                            <meta property="og:url" content="https://site.com/' . $result['file_dir'] . '/' . $result['file_name'] . '">
                            <meta property="og:title" content="Download file ' . $result['file_rename'] . '">
                            <meta property="og:description" content="Download the '.$result['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">;
                            <meta property="og:locale" content="en_US">';
                        }
                    
                    echo "<style>
                        body, html {
                            margin: 0;
                            padding: 0;
                            height: 100%;
                            overflow: hidden;
                        }
                        #countdown {
                            position: absolute;
                            top: 10px;
                            left: 50%;
                            transform: translateX(-50%);
                            font-size: 20px;
                            background-color: #f0f0f0;
                            padding: 5px 10px;
                            border-radius: 5px;
                        }
                        #frame {
                            width: 100%;
                            height: calc(100%);
                            border: none;
                        }
                    </style>
                </head>
                <body>
                    <div id=\"countdown\">You will be redirected in 15 seconds.</div>
                    <iframe id=\"frame\" src=\"//site.com/ads\" frameborder=\"0\" sandbox></iframe>
                    <script>
                        var countdown = 15;
                        var countdownDisplay = document.getElementById('countdown');
                        var countdownInterval = setInterval(function() {
                            countdown--;
                            countdownDisplay.innerHTML = 'You will be redirected in ' + countdown + ' seconds.';
                            if (countdown <= 0) {
                                clearInterval(countdownInterval);
                                var redirectURL = 'https://site.com/file?id=' + encodeURIComponent('$file_id');
                                window.location.href = redirectURL;
                            }
                        }, 1000);
                    </script>
                </body>
                </html>
                ";
                exit();

            }
        }
    }

    // Ссылка не найдена - перенаправляем на другую страницу или выводим уведомление
    header("Location: https://site.com/");
    exit();
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
