<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = 'INSERT INTO chat (room, roomtype, user, message) VALUES (?, ?, ?, ?)';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssss', $room, $roomtype, $user, $message);
        $user = $_SESSION['username'];
        $message = $_POST['message'];
        $room = $_POST['room'];
        $roomtype = $_POST['roomtype'];
        echo "$room / $roomtype / $user / $message";
        if (mysqli_stmt_execute($stmt)) {
            die('Success');
        }

        die('Fail');
    }
    mysqli_stmt_close($stmt);
}
