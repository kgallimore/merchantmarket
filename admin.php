<?php
session_start();
include_once 'header.php';

if ($_SESSION['role'] !== 'admin' || !isset($_SESSION['role'])) {
    header('location: index.php');
    exit;
}

?>

<html lang="en">
<header><title>This is title</title></header>
<body>
Hello world
<?php
include_once 'footer.php';
