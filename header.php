<?php
require_once 'config/config.php';
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="main.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="node_modules/socket.io/lib/socket.js"></script>
    <script src="list.min.js" type="text/javascript"></script>
    
    <script>
        function expand(button) {
            button.classList.toggle("active");
            const content = button.nextElementSibling;
            if (content.style.maxHeight){
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
            }
        }
    </script>
</head>
<body>';
echo '<a href="https://trello.com/b/dPJk0EiI/maraduers-market" class="btn btn-default">Site Roadmap</a><br>';
if ($_SERVER['REQUEST_URI'] !== '/index.php') {
    echo '<a href="index.php" class="btn btn-success">Home</a>';
}
if ($_SESSION['role'] === 'merchant' || $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_URI'] !== '/merchantmanage.php') {
        echo '<a href="merchantmanage.php" class="btn btn-primary ">Manage Market</a>';
    }
    if ($_SERVER['REQUEST_URI'] !== '/customerRequests.php') {
        $sql = 'SELECT * FROM service_requests WHERE merchant = ? AND last_see_merch < last_update';

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 's', $username);
            $username = $_SESSION['username'];
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                $number_requests = mysqli_stmt_num_rows($stmt);
                if ($number_requests > 0) {
                    echo "<a href=\"customerRequests.php\" class=\"btn btn-warning \">Customer Request updates: $number_requests</a>";
                } else {
                    echo "<a href=\"customerRequests.php\" class=\"btn btn-primary \">Customer Request updates: $number_requests</a>";
                }
                mysqli_stmt_close($stmt);

            }
        }
    }
} elseif ($_SERVER['REQUEST_URI'] !== '/requestMerchant.php') {
    echo '<a href="requestMerchant.php" class="btn btn-primary ">Request to be a merchant</a>';
}
if ($_SERVER['REQUEST_URI'] !== '/requests.php') {
    $sql = 'SELECT * FROM service_requests WHERE customer = ? AND last_seen_cust < last_update';

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, 's', $username);
        $username = $_SESSION['username'];
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Store result
            mysqli_stmt_store_result($stmt);

            // Check if username exists, if yes then verify password
            $number_requests = mysqli_stmt_num_rows($stmt);
            if ($number_requests > 0) {
                echo "<a href=\"requests.php\" class=\"btn btn-warning \">Request updates: $number_requests</a>";
            } else {
                echo "<a href=\"requests.php\" class=\"btn btn-primary \">Request updates: $number_requests</a>";
            }

        }
    }
}

echo '</p>';
if ((!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true)) {
    echo '<a href="register.php" class="btn btn-warning">Register an account</a><a href="login.php" class="btn btn-danger">Login</a>';
} else {
    echo '<a href="reset-password.php" class="btn btn-warning">Reset Your Password</a><a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>';
}
echo '<div class="page-header"><h1>Hello, <b>';
if ((!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true)) {
    echo 'Guest';
} else {
    echo htmlspecialchars($_SESSION['username']);
}
echo '</b>.</h1></div><p><br>';
