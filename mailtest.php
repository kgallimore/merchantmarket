<?php
include_once 'header.php';
if ($_SESSION['role'] === 'admin'){
    mail(MAIL_TO, 'Test Email', 'This is a test to see if the email goes through');
}

