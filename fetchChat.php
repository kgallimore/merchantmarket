<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/config.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = "SELECT user, message, timesent FROM chat WHERE room = ? AND roomtype = ? AND timesent <= UNIX_TIMESTAMP() ORDER BY timesent LIMIT 15";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $room, $roomtype);
        $room = $_POST['room'];
        $roomtype = $_POST['roomtype'];
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $user, $message, $timesent);
            while (mysqli_stmt_fetch($stmt)) {
                echo "<b>$user:</b> $message<br>";
            }
        }
    }
    mysqli_stmt_close($stmt);
}
