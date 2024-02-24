<?php
include_once __DIR__ . '/../includes/settings.php';

if (isset($_GET['id'])) {$file_id = $_GET['id'];
}

$stmt = $conn->prepare('SELECT * FROM files WHERE file_id = :file_id');
$stmt->execute(array(':file_id' => $file_id));
$file = $stmt->fetch(PDO::FETCH_ASSOC);

// Assuming you have the $file['user_id'] available
$user_id = $file['user_id'];

// Fetch user's plan details from the database
$stmt = $conn->prepare('SELECT delete_time FROM plans WHERE id IN (SELECT owner_plan FROM files WHERE file_id = :file_id)');
$stmt->execute(array(':file_id' => $file_id));
$user_plan = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the plan details were fetched successfully
if ($user_plan) {$delete_time = $user_plan['delete_time'];// Calculate the deletion date based on the user's plan$deletionDate = $file['date'] + $delete_time;$tmdlt = $delete_time / 86400;// Now, use $deletionDate in your JavaScript to update the timer
} else {// Handle the case where the user's plan details were not found
}

$imageTypes = ["jpg" => "image/jpeg","jpeg" => "image/jpeg","png" => "image/png","gif" => "image/gif","svg" => "image/svg+xml","webp" => "image/webp","avif" => "image/avif","apng" => "image/apng","ico" => "image/vnd.microsoft.icon","bmp" => "image/bmp","tiff" => "image/tiff","tif" => "image/tiff",// Добавьте другие форматы изображений и их типы по необходимости
];

$fileTypes = ["doc" => "application/msword","docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document","pdf" => "application/pdf","txt" => "text/plain","rtf" => "application/rtf","html" => "text/html","xls" => "application/vnd.ms-excel","xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","ppt" => "application/vnd.ms-powerpoint","pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation","zip" => "application/zip",// Добавьте другие форматы файлов и их типы по необходимости
];
?>
<?php if ($file): ?><!DOCTYPE html>
<html  lang="en">
<head>
  
  
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="assets/images/circle-logo-96x96.png" type="image/x-icon">
  <meta property="og:site_name" content="PLTFRM">
  <meta property="og:title" content="Download file <?php echo $file['file_rename']; ?>">
  
  
  
  <title><?php echo $file['file_rename']; ?> | Download Page</title>
  <link rel="stylesheet" href="assets/font-awesome-solid/../css/fontawesome.min.css">
  <link rel="stylesheet" href="assets/font-awesome-solid/css/solid.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="assets/tether/tether.min.css">
  <link rel="stylesheet" href="assets/dropdown/css/style.css">
  <link rel="stylesheet" href="assets/socicon/css/styles.css">
  <link rel="stylesheet" href="assets/theme/css/style.css">
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Gasoek+One:400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Gasoek+One:400&display=swap"></noscript>
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Bebas+Neue:400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas+Neue:400&display=swap"></noscript>
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Jost:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Jost:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>
  <link rel="preload" as="style" href="assets/mobirise/css/mbr-additional.css"><link rel="stylesheet" href="assets/mobirise/css/mbr-additional.css" type="text/css">
  
  <?php$fileType = pathinfo(strtolower($file["file_name"]), PATHINFO_EXTENSION);if (array_key_exists($fileType, $imageTypes)) {    $imageType = $imageTypes[$fileType];        $urlimgpath = 'site.com' . $file['file_dir'] . '/' . $file['file_name'];        // Создание нового объекта Imagick    $image = new Imagick($urlimgpath);        // Получение ширины и высоты изображения    $width = $image->getImageWidth();    $height = $image->getImageHeight();    echo '<!-- Open Graph for images -->    <meta property="og:type" content="image">    <meta property="og:image" content="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '">    <meta property="og:image:secure_url" content="site.comdownload.php?id='.$file["file_id"].'">    <meta property="og:image:width" content="'.$width.'">    <meta property="og:image:height" content="'.$height.'">    <meta property="og:image:type" content="' . $imageType . '"> <!-- Тип изображения -->';        echo '  <!-- Twitter Meta Tags -->      <meta name="twitter:card" content="summary_large_image">      <meta property="twitter:domain" content="site.com">      <meta property="twitter:url" content="https://site.com/file?id='.$file_id.'">      <meta name="twitter:title" content="Download file ' . $file['file_rename'] . '">      <meta name="twitter:description" content="Download the '.$file['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">      <meta name="twitter:image" content="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '">';} elseif (array_key_exists($fileType, $fileTypes)) {    $fileType = $fileTypes[$fileType];    echo '<!-- Open Graph for files -->    <meta property="og:type" content="object">    <meta property="og:url" content="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '">    <meta property="og:title" content="Download file ' . $file['file_rename'] . '">    <meta property="og:description" content="Download the '.$file['file_rename'].' file within '.$tmdlt.' days. PLTFRM will help you share files on the internet.">;    <meta property="og:locale" content="en_US">';}?>
  
  
</head>
<body>
  <script src="https://app.embed.im/snow.js" defer></script>
  <section data-bs-version="5.1" class="menu menu2 cid-tZaHrQqon0" once="menu" id="menu2-z"><nav class="navbar navbar-dropdown navbar-fixed-top navbar-expand-lg">    <div class="container">        <div class="navbar-brand">            <span class="navbar-logo">                <a href="index.html">                    <img src="assets/images/circle-logo-96x96.png" alt="" style="height: 3rem;">                </a>            </span>            <span class="navbar-caption-wrap"><a class="navbar-caption text-black text-primary display-7" href="index.html">PLTFRM</a></span>        </div>        <button class="navbar-toggler" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#navbarSupportedContent" data-bs-target="#navbarSupportedContent" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">            <div class="hamburger">                <span></span>                <span></span>                <span></span>                <span></span>            </div>        </button>        <div class="collapse navbar-collapse" id="navbarSupportedContent">            <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true"><li class="nav-item"><a class="nav-link link text-black text-primary display-4" href="faq.html">FAQ</a></li>                <li class="nav-item"><a class="nav-link link text-black text-primary display-4" href="terms.html">Terms</a>                </li></ul>                        <div class="navbar-buttons mbr-section-btn"><a class="btn btn-primary display-4" href="https://t.me/123456bot" target="_blank">                    GET STARTED</a></div>        </div>    </div></nav>
</section>

<section class="mbr-section" id="witsec-modal-window-block-13" data-rv-view="557">

	<style>
	/* Let's not animate the contents of modal windows */
	.no-anim {
		-webkit-animation: none !important;
		-moz-animation: none !important;
		-o-animation: none !important;
		-ms-animation: none !important;
		animation: none !important;
	}
	</style>

	
	
	<div><div class="modal fade" id="tima" tabindex="-1" role="dialog" aria-labelledby="timaLabel" aria-hidden="true">  <div class="modal-dialog  modal-dialog-centered" style="height:auto" role="document">    <div class="modal-content"><div class="modal-header">  <h5 class="no-anim modal-title display-7" id="timaLabel"></h5>  <a href="#" class="no-anim close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a></div><div class="modal-body display-7" id="tima_body">Still not started downloading?   Try this method</div><div class="modal-footer"><div class="mbr-section-btn"><a href="site.com<?php echo $file['file_dir'] . '/' . $file['file_name']; ?>" class="no-anim btn btn-primary display-4" target="_new">Download</a></div></div>    </div>  </div></div><script> 
document.addEventListener("DOMContentLoaded", function() { 
  if(typeof jQuery === "function") {$("#tima").on("hidden.bs.modal", function () {   var html = $( "#tima_body" ).html();   $( "#tima_body" ).empty();   $( "#tima_body" ).append(html); }) 
  } else {   var mdw = document.getElementById("#tima")   mdw.addEventListener("hidden.bs.modal", function(event) {     mdw.innerHTML = mdw.innerHTML;   }); 
  } 
}); 
</script></div>

	<script>
	if (typeof OpenModal === 'undefined') {
		OpenModal = function(modalName) {
			if(typeof jQuery === "function") {
				if ($('#' + modalName).length)
					$('#' + modalName).modal('show');
				else
					alert("Sorry, but there is no modal for " + modalName);
			} else {
				let mdw = new bootstrap.Modal(document.getElementById(modalName), {});
				mdw.show();
			}
		}
	}

	function modalSetCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

	function modalGetCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}
	</script>

</section>

<section data-bs-version="5.1" class="header11 cid-tZmsEAG5Kr" id="header11-11">


<div class="container">    <div class="row justify-content-center">        <div class="col-12 col-md-6 image-wrapper">            <?php                    $fileType = pathinfo(strtolower($file['file_name']), PATHINFO_EXTENSION);                    if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif', 'apng', 'ico'])) {                        echo '<img class="w-100" src="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '" alt="File Preview">';                    } elseif (in_array($fileType, ['mp4', 'mov', 'ogg', 'webm', 'mkv', 'flv', 'avi', 'm4v'])) {                        echo '<video controls>                        <source class="w-100" src="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '"alt="File Preview">                          <div class="alert alert-warning" role="alert">Your browser does not support the video preview.</div>                          </video>';                    } elseif (in_array($fileType, ['m4a', 'flac', 'mp3', 'wav', 'aac', 'wma'])) {                        echo '<audio controls>                        <source class="w-100" src="site.com' . $file['file_dir'] . '/' . $file['file_name'] . '" alt="File Preview">                          <div class="alert alert-warning" role="alert">Your browser does not support the video preview.</div>                          </audio>';                    } elseif (in_array($fileType, ['doc', 'docx', 'dot', 'html', 'txt', 'rtf', 'odt', 'pdf', 'pages', 'xps', 'dotx', 'ott', 'psw', 'prn', 'stw', 'vor', 'uot', 'xml', 'xls', 'xlsx', 'sdc', 'csv', 'chm', 'dbf', 'dif', 'djvu', 'hlp', 'ods', 'ots', 'pxl', 'sdc', 'stc', 'sxc', 'slk', 'uos', 'xlt', 'xls', 'xlsm', 'ppt', 'pptx', 'sdd', 'sxi', 'odg', 'odp', 'otp', 'pot', 'pptm', 'ppsx', 'sdd', 'sda', 'sti', 'uop', 'dfx', 'eps', 'psd', 'ps', 'svg', 'tif', 'ttf', 'ai', 'bpm', 'dwg', 'emf', 'eot', 'fxg', 'met', 'otf', 'pbm', 'pct', 'pgm', 'ppm', 'pwp', 'ras', 'svm', 'swf', 'sxd', 'svgz', 'jpf', 'psb', 'raw', 'sct', 'woff', 'zip'])) {                        echo '<iframe class="w-100" src="http://docs.google.com/gview?url=site.com' . $file['file_dir'] . '/' . $file['file_name'] . '&embedded=true" 
style="max-width:650px; max-height:650px; width:500px; height:550px;" frameborder="0"></iframe>';                    } else {                        // Handle other file types (videos, documents, etc.) as needed                        echo '<div class="col-12 col-md-8"><blockquote><p class="mbr-text mbr-fonts-style" mbr-theme-style="display-4" data-app-selector=".mbr-text"><br>Preview not available for this file type.&nbsp;<br></p></blockquote></div>';                    }                    ?>        </div>        <div class="col-12 col-md">            <div class="text-wrapper text-center">                <h1 class="mbr-section-title mbr-fonts-style mb-3 display-3">                    <strong><?php echo $file['file_rename']; ?></strong></h1>                <p class="mbr-text mbr-fonts-style display-7">                    <?php echo "ID: " . $file['origin_file_id']; ?></p>                <div class="mbr-section-btn mt-3">                    <a class="btn btn-danger display-7" target="_blank" href="https://ronypo.kiro.pw/go/mq2tcytdg45dcnzsgy4q?name=<?php echo $file['file_rename']; ?>&id=<?php echo $file['origin_file_id']; ?>&tariff=<?php echo $file['owner_plan']; ?>&origname=<?php echo $file['file_name']; ?>&deeplink=site.comloading.php?postid=<?php echo $file["file_id"]; ?>" download="">Download</a>                <?php include 'report.php'; ?></div>            </div>        </div>    </div></div>
</section>

<section class="_customHTML cid-tZuZXWT6CO" id="patterns-19">
  <div>     <div class="custom-shape-divider"><svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path opacity=".5" d="M0 86.9V74.7l220.7-19.3L0 29.2V17.9l250 38-250 31z" class="shape-fill"></path><path opacity=".5" d="M0 74.7v-8.8l145.2-12.7L0 36v-6.8l220.7 26.2L0 74.7z" class="shape-fill"></path><path opacity=".5" d="M0 74.7v-8.8l145.2-12.7L0 36v-6.8l220.7 26.2L0 74.7z" class="shape-fill"></path><path opacity=".5" d="M0 65.9V36l145.2 17.2L0 65.9z" class="shape-fill"></path><path opacity=".5" d="M0 65.9V36l145.2 17.2L0 65.9z" class="shape-fill"></path><path opacity=".75" d="M0 65.9V36l145.2 17.2L0 65.9z" class="shape-fill"></path><path opacity=".5" d="M750 55.9l250-38v12.3L779.3 56.4 1000 75.7v11.2l-250-31zM500 18L250 56l250 31 250-31zM279.3 56.3L500 30.2l220.7 26.2L500 75.7z" class="shape-fill"></path><path d="M500 17.9l-250 38-250-38v-18h1000v18l-250 38-250-38z" class="shape-fill"></path><path d="M500 30.2L279.3 56.4 500 75.7l220.7-19.3zM335.4 55.5L500 36l164.6 19.5L500 70z" opacity=".5" class="shape-fill"></path><path d="M500 30.2L279.3 56.4 500 75.7l220.7-19.3zM335.4 55.5L500 36l164.6 19.5L500 70z" opacity=".5" class="shape-fill"></path><path opacity=".5" d="M664.6 55.5L500 69.9 335.4 55.5 500 36l164.6 19.5z" class="shape-fill"></path><path opacity=".5" d="M664.6 55.5L500 69.9 335.4 55.5 500 36l164.6 19.5z" class="shape-fill"></path><path opacity=".75" d="M664.6 55.5L500 69.9 335.4 55.5 500 36l164.6 19.5z" class="shape-fill"></path><path opacity=".5" d="M1000 30.2V36L854.9 53.2 1000 65.9v9.8L779.3 56.4 1000 30.2z" class="shape-fill"></path><path opacity=".5" d="M1000 30.2V36L854.9 53.2 1000 65.9v9.8L779.3 56.4 1000 30.2z" class="shape-fill"></path><path opacity=".5" d="M1000 36v29.9L854.9 53.2 1000 36z" class="shape-fill"></path><path opacity=".5" d="M1000 36v29.9L854.9 53.2 1000 36z" class="shape-fill"></path><path opacity=".75" d="M1000 36v29.9L854.9 53.2 1000 36z" class="shape-fill"></path>
</svg></div>
</div>  
</section>

<section data-bs-version="5.1" class="content6 cid-tZv0EmKiuD" id="content6-1a">
<style>/* Центрирование элементов */.centered-ad {    display: flex;    justify-content: center;    align-items: center;}
</style><div class="container centered-ad">    <div class="row justify-content-center">        <div class="col-md-12 col-lg-10">            <hr class="line">            <p class="mbr-text align-center mbr-fonts-style my-4 display-5">                YOUR ADS HERE            </p>            <hr class="line">        </div>    </div></div>
</section>

<section data-bs-version="5.1" class="features14 cid-tZmsK6udyc" id="features14-12">
<div class="container">    <div class="row mb-5">        <div class="col-12">            <h3 class="mbr-section-title align-center mbr-fonts-style display-2"><strong>DETAILS</strong></h3>        </div>    </div>    <div class="row">                <div class="item features-without-image mb-5 col-12 col-md-2 active">            <div class="item-wrapper">                <div class="card-box align-center">                    <span class="mbr-iconfont fas fa-trash-can"></span>                    <h4 class="card-title align-center mbr-black mbr-fonts-style display-7">                        <strong id="timer">delete time</strong></h4>                </div>            </div>        </div>        <div class="item features-without-image mb-5 col-12 col-md-2">            <div class="item-wrapper">                <div class="card-box align-center">                    <span class="mbr-iconfont fas fa-weight-scale"></span>                    <h4 class="card-title align-center mbr-black mbr-fonts-style display-7">                        <strong><?php                        $fileSizeInBits = $file['file_size'];
                        if ($fileSizeInBits < 1048576) {                            $fileSizeInKilobytes = $fileSizeInBits / 1024; // 1,024 bits = 1 KB                            $fileSizeInKilobytesRounded = round($fileSizeInKilobytes, 2); // Rounded to two decimal places                                                if ($fileSizeInKilobytes < 1) {                                echo $fileSizeInBits . " bytes";                            } else {                                echo $fileSizeInKilobytesRounded . " KB";                            }                        } else {                            $fileSizeInMegabytes = $fileSizeInBits / 1048576; // 1,048,576 bits = 1 MB                            $fileSizeInMegabytesRounded = round($fileSizeInMegabytes, 2); // Rounded to two decimal places                                                echo $fileSizeInMegabytesRounded . " MB";                        }                        ?></strong></h4>                </div>            </div>        </div>        <div class="item features-without-image mb-5 col-12 col-md-2">            <div class="item-wrapper">                <div class="card-box align-center">                    <span class="mbr-iconfont fas fa-eye"></span>                    <h4 class="card-title align-center mbr-black mbr-fonts-style display-7">                        <strong><?php                        // Получение даты загрузки файла из временной метки (timestamp)                        $uploadDate = $file["date"]; // Предположим, что это временная метка UNIX
                        // Преобразуем временные метки в формат Y-m-d для использования в запросе к API                        $fromDate = date("Y-m-d", $uploadDate);                        $toDate = date("Y-m-d", $deletionDate);
                        // Создаем URL запроса к API с учетом временного интервала                        $api_url = "https://fy.oo.gd/api/v1/stats/10?name=page&per_page=100&from=$fromDate&to=$toDate";
                        $api_key =                            "WKDRKItubVZNcTWirjvlDnD4F8N5iXVOQHlPtqasb7RjUCIh8xGKuoDet1DOQhDB"; // Замените YOUR_API_KEY на ваш API ключ
                        $ch = curl_init($api_url);                        curl_setopt($ch, CURLOPT_HTTPHEADER, [                            "Accept: application/json",                            "Authorization: Bearer " . $api_key,                        ]);                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                        $response = curl_exec($ch);
                        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {                            $data = json_decode($response, true);
                            // Обработка данных: просмотры для данного файла                            $file_views = 0;                            foreach ($data["data"] as $item) {                                if (                                    $item["value"] ===                                    "/file?id=" . $file_id                                ) {                                    $file_views = $item["count"];                                    break;                                }                            }
                            // Отображение количества просмотров на странице                            echo "File Views: $file_views";
                        } else {                            echo "Failed to fetch views data.";                        }                        ?></strong></h4>                </div>            </div>        </div>                    </div></div>
</section>

<section data-bs-version="5.1" class="footer7 cid-tZb7VXDi53" once="footers" id="footer7-10">


<div class="container">    <div class="media-container-row align-center mbr-white">        <div class="col-12">            <p class="mbr-text mb-0 mbr-fonts-style display-7">                © Copyright 2023 PLTFRM (Chiminori) - All Rights Reserved            </p>        </div>    </div></div>
</section><section><a href="https://mobiri.se"></a><a href="https://mobiri.se"></a></section><script src="assets/popper/popper.min.js"></script>  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>  <script src="assets/smoothscroll/smooth-scroll.js"></script>  <script src="assets/ytplayer/index.js"></script>  <script src="assets/tether/tether.min.js"></script>  <script src="assets/dropdown/js/navbar-dropdown.js"></script>  <script src="assets/theme/js/script.js"></script>  
  
  <script>    // Функция для обновления таймера    function updateTimer() {        var deletionDate = <?php echo $deletionDate; ?>; // Добавляем 7 дня к времени загрузки файла        var now = Math.floor(Date.now() / 1000); // Текущее время в секундах
        var timeLeft = deletionDate - now;        if (timeLeft > 0) {            var days = Math.floor(timeLeft / (60 * 60 * 24));            var hours = Math.floor((timeLeft % (60 * 60 * 24)) / (60 * 60));            var minutes = Math.floor((timeLeft % (60 * 60)) / 60);            var seconds = Math.floor(timeLeft % 60);
            document.getElementById('timer').innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s";        } else {            document.getElementById('timer').innerHTML = "File has been deleted";        }    }
    // Обновляем таймер каждую секунду    setInterval(updateTimer, 1000);</script><!-- Скрипт для отслеживания времени после нажатия на кнопку -->
<script>var downloadButton = document.getElementById('downloadLink');var downloadSection = document.getElementById('download-section');var timerInterval;var secondsElapsed = 0;
downloadButton.addEventListener('click', function (event) {    event.preventDefault(); // Отменяем стандартное действие кнопки
    // Начинаем отсчёт времени после нажатия на кнопку    timerInterval = setInterval(function () {        secondsElapsed++;        if (secondsElapsed === 10) {            clearInterval(timerInterval); // Останавливаем отсчёт после 10 секунд            // Показываем текст через 10 секунд, предлагающий альтернативную ссылку            OpenModal('tima');        }    }, 1000);});
</script>
</body>
</html>
<?php
else: 
header("Location: https://site.com/");
endif; ?>