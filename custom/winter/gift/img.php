<?php
$servername = "localhost"; // Имя сервера базы данных
$username = "mrblue_root"; // Имя пользователя базы данных
$password = "VErj014UVqR7Afu47z"; // Пароль для доступа к базе данных
$dbname = "mrblue_FileWithURLBot"; // Имя базы данных

try {$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);// Установка режима ошибок PDO в исключения$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {echo "Connection failed: " . $e->getMessage(); // Вывод сообщения об ошибке при неудачном подключении
}

function decryptLettersToDigits($input) {$letterToDigit = ['A' => '0', 'B' => '1', 'C' => '2', 'D' => '3', 'E' => '4', 'F' => '5', 'G' => '6', 'H' => '7', 'I' => '8', 'J' => '9'];$decrypted = strtr($input, $letterToDigit);return $decrypted;
}

if (isset($_GET['key']) && ctype_alpha($_GET['key'])) {$encryptedKey = $_GET['key'];
$user_id = decryptLettersToDigits($encryptedKey);
// Check if the user exists in the database$stmt = $conn->prepare('SELECT * FROM users WHERE chat_id = :user_id AND ban = 0');$stmt->execute(array(':user_id' => $user_id));$user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {die("User not found or blocked.");
}


$imageName = isset($_GET['img']) ? $_GET['img'] : null;

if ($imageName) {$imagePath = $imageName; // Путь к изображению, если он расположен в той же директории, где находится image_handler.php
// Установка заголовка Content-Type для изображения$mime = mime_content_type($imagePath);header("Content-Type: $mime");
// Чтение содержимого изображения в бинарном виде$imageData = file_get_contents($imagePath);
// Отправка данных в виде blobecho $imageData;
} else {// В случае, если имя изображения не было передано или некорректно, можно вернуть ошибку или другое изображение по умолчаниюhttp_response_code(404);exit;
}
?>
