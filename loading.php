<?php
include_once __DIR__ . '/../includes/settings.php';

if (isset($_GET['postid'])) {$file_id = $_GET['postid'];
}

$stmt = $conn->prepare('SELECT * FROM files WHERE file_id = :file_id');
$stmt->execute(array(':file_id' => $file_id));
$file = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<?php if ($file): ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>File preparation</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-green-200"><div class="container mx-auto my-10 p-8 bg-white rounded shadow-lg">    <h1 class="text-2xl font-bold mb-4">File preparation</h1>    <p class="text-gray-700 mb-4">Your file is being prepared for download. Click on the green button to start the download.</p>        <form id="downloadForm" method="post" action="site.comdownload">        <input type="hidden" name="id" value="<?php echo $file["file_id"]; ?>">        <button id="downloadButton" class="bg-yellow-500 text-white px-4 py-2 rounded" disabled>Download</button>    </form>
    <div id="timer" class="text-xl font-bold text-gray-700 mt-4">Left: <span id="countdown">10</span> sec.</div>
    <script>        // JavaScript для таймера        let countdown = 10;
        function updateCountdown() {            document.getElementById('countdown').innerText = countdown;            countdown--;
            if (countdown < 0) {                document.getElementById('downloadButton').disabled = false;                document.getElementById('timer').style.display = 'none';                document.getElementById('downloadButton').classList.remove('bg-yellow-500');                document.getElementById('downloadButton').classList.add('bg-green-500');            } else {                setTimeout(updateCountdown, 1000);            }        }
        updateCountdown();
        // Добавим обработчик формы после окончания таймера        setTimeout(function () {            document.getElementById('downloadForm').addEventListener('submit', function () {                // Если вы хотите выполнить какие-то дополнительные действия перед отправкой формы, вы можете сделать это здесь            });        }, countdown * 1000);    </script></div>
</body>
</html>
<?php
else: 
header("Location: https://site.com/");
endif; ?>