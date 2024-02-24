<?php
// Подключение к базе данных и другие необходимые настройки
include_once __DIR__ . '/../includes/settings.php';

// Проверяем, есть ли GET-параметр "id"
if (isset($_GET['id'])) {$file_id = $_GET['id'];
$stmt = $conn->prepare('SELECT * FROM files WHERE file_id = :file_id');$stmt->execute(array(':file_id' => $file_id));$file = $stmt->fetch(PDO::FETCH_ASSOC);
if ($file) {    $file_path = $file['file_dir'] . '/' . $file['file_name'];
    if (file_exists($file_path)) {        $file_size = filesize($file_path);        $fp = fopen($file_path, 'rb');                $offset = 0;        $length = $file_size;
        if (isset($_SERVER['HTTP_RANGE'])) {            $range = $_SERVER['HTTP_RANGE'];            $partial = true;
            list($param, $range) = explode('=', $range);            if (strtolower(trim($param)) != 'bytes') {                header("HTTP/1.1 400 Invalid Request");                exit;            }
            list($from, $to) = explode('-', $range);            if ($from === '') {                $end = $file_size - 1;                $start = $end - intval($from);            } else if ($to === '' || $to > $file_size - 1) {                $start = intval($from);                $end = $file_size - 1;            } else {                $start = intval($from);                $end = intval($to);            }
            $offset = $start;            $length = $end - $start + 1;            fseek($fp, $offset);            header('HTTP/1.1 206 Partial Content');            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size);        } else {            header('HTTP/1.1 200 OK');        }
        header('Content-Description: File Transfer');        header('Content-Type: application/octet-stream');        header('Content-Disposition: attachment; filename="' . $file['file_rename'] . '"');        header('Expires: 0');        header('Cache-Control: must-revalidate');        header('Pragma: public');        header('Content-Length: ' . $length);
        fseek($fp, $offset);
        while (!feof($fp) && ($remaining = $length > 0 ? $length : 1024 * 8)) {            echo fread($fp, $remaining);            $length -= $remaining;        }
        fclose($fp);        exit;    } else {        echo 'File not found.';    }} else {    echo 'File information not found.';}
} elseif (isset($_POST['id'])) {$file_id = $_POST['id'];
$stmt = $conn->prepare('SELECT * FROM files WHERE file_id = :file_id');$stmt->execute(array(':file_id' => $file_id));$file = $stmt->fetch(PDO::FETCH_ASSOC);
if ($file) {    $file_path = $file['file_dir'] . '/' . $file['file_name'];
    if (file_exists($file_path)) {        $file_size = filesize($file_path);        $fp = fopen($file_path, 'rb');                $offset = 0;        $length = $file_size;
        if (isset($_SERVER['HTTP_RANGE'])) {            $range = $_SERVER['HTTP_RANGE'];            $partial = true;
            list($param, $range) = explode('=', $range);            if (strtolower(trim($param)) != 'bytes') {                header("HTTP/1.1 400 Invalid Request");                exit;            }
            list($from, $to) = explode('-', $range);            if ($from === '') {                $end = $file_size - 1;                $start = $end - intval($from);            } else if ($to === '' || $to > $file_size - 1) {                $start = intval($from);                $end = $file_size - 1;            } else {                $start = intval($from);                $end = intval($to);            }
            $offset = $start;            $length = $end - $start + 1;            fseek($fp, $offset);            header('HTTP/1.1 206 Partial Content');            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size);        } else {            header('HTTP/1.1 200 OK');        }
        header('Content-Description: File Transfer');        header('Content-Type: application/octet-stream');        header('Content-Disposition: attachment; filename="' . $file['file_rename'] . '"');        header('Expires: 0');        header('Cache-Control: must-revalidate');        header('Pragma: public');        header('Content-Length: ' . $length);
        fseek($fp, $offset);
        while (!feof($fp) && ($remaining = $length > 0 ? $length : 1024 * 8)) {            echo fread($fp, $remaining);            $length -= $remaining;        }
        fclose($fp);        exit;    } else {        echo 'File not found.';    }} else {    echo 'File information not found.';}
} else {echo 'Invalid file ID.';
}
?>