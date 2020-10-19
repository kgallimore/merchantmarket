<?php
include_once 'header.php';
if ($_SESSION['role'] === 'admin'){
    phpinfo();
}

