<?php
$servername = "localhost"; // Имя сервера базы данных
$username = "mrblue_root"; // Имя пользователя базы данных
$password = "VErj014UVqR7Afu47z"; // Пароль для доступа к базе данных
$dbname = "mrblue_FileWithURLBot"; // Имя базы данных

try {$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);// Установка режима ошибок PDO в исключения$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {echo "Connection failed: " . $e->getMessage(); // Вывод сообщения об ошибке при неудачном подключении
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {$user_id = $_GET['id'];
// Check if the user exists in the database$stmt = $conn->prepare('SELECT * FROM users WHERE chat_id = :user_id AND ban = 0');$stmt->execute(array(':user_id' => $user_id));$user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {die("User not found or blocked.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Get Promocode</title>
   <?php include_once 'protect.php'; ?>
<script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script  src='style.js'></script><script  src='style.js'></script><script  src='style.js'></script><script  src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src='style.js'></script><script disable-devtool-auto src="style.js"></script><script disable-devtool-auto src="style.js"></script><script disable-devtool-auto src="style.js"></script><script disable-devtool-auto src="style.js"></script></head>
<body>
  <div class="snowflakes" aria-hidden="true"><!-- Снежинки будут добавлены здесь с помощью JavaScript -->
  </div>

  <div class="card" id="myCard"><div class="front" onclick="rotateCard()"><img src="img.php?img=data/card.png&key=<?php echo encryptDigitsToLetters($user_id); ?>"></div><div class="back" onclick="rotateCard()"><img src="img.php?img=data/card-bg.png&key=<?php echo encryptDigitsToLetters($user_id); ?>"></div>
  </div>

  <script>let isCardFront = true;
function rotateCard() {  const card = document.getElementById('myCard');  if (isCardFront) {    card.style.transform = 'rotateY(-180deg)';  } else {    card.style.transform = 'rotateY(0deg)';  }  isCardFront = !isCardFront;}
function createSnowflakes() {  const snowflakeContainer = document.querySelector('.snowflakes');
  for (let i = 0; i < 50; i++) {    const snowflake = document.createElement('div');    snowflake.className = 'snowflake';    snowflake.style.left = Math.random() * 100 + 'vw';    snowflake.style.animationDuration = Math.random() * 3 + 2 + 's';    snowflake.style.animationDelay = Math.random() + 's';
    snowflakeContainer.appendChild(snowflake);  }}
window.addEventListener('load', createSnowflakes);
  </script>
</body>
</html>
