<?php
$possibleContactOptions = ['discord_user','telegram_user','skype','email','other'];
$possibleContactOptionsMerch = array_merge($possibleContactOptions, ['discord_server_widget','discord_server','telegram_group','skype_chat','website']);
function compare_time($time){
    $timeDiff = time() - $time;
    if ($timeDiff < 30) {
        return 'Now';
    }

    if ($timeDiff < 60) {
        return '1 minute ago';
    }

    if ($timeDiff < 3600) {
        $extraTime = round($timeDiff / 60);
        return "$extraTime minutes ago";
    }

    if ($timeDiff < 5400) {
        $extraTime = round($timeDiff / 3600);
        return '1 hour ago';
    }

    if ($timeDiff < 172800) {
        $extraTime = round($timeDiff / 3600);
       return "$extraTime hours ago";
    }

    if ($time === 0) {
        return 'Never';
    }

    $extraTime = round($timeDiff / 86400);
    return "$extraTime days ago";
}
function is_session_started()
{
    if (PHP_SAPI !== 'cli') {
        if (PHP_VERSION_ID >= 50400) {
            return session_status() === PHP_SESSION_ACTIVE;
        }

        return session_id() !== '';
    }
    return FALSE;
}


function detectMobile(){
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}



if (is_session_started() === FALSE) {
    session_start();
}
$headers = array('CLIENT_IP', 'FORWARDED', 'FORWARDED_FOR', 'FORWARDED_FOR_IP', 'VIA', 'X_FORWARDED', 'X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED_FOR_IP', 'HTTP_PROXY_CONNECTION', 'HTTP_VIA', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR');
foreach ($headers as $header) {
    if (isset($_SERVER[$header])) {
        die('Proxy access not allowed.');
    }
}
if ((!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) && $_SERVER['REQUEST_URI'] !== '/login.php' && $_SERVER['REQUEST_URI'] !== '/register.php' && $_SERVER['REQUEST_URI'] !== '/merchant.php?merchantName=edward') {
    header('location: login.php');
    exit;
}


include_once 'details.php';
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if ($link === false) {
    die('ERROR: Could not connect. ' . mysqli_connect_error());
}

function refreshSeenTime($link)
{
    $sql2 = 'UPDATE users SET last_seen = unix_timestamp() WHERE username = ?';
    if ($stmt2 = mysqli_prepare($link, $sql2)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt2, 's', $username);
        $username = $_SESSION['username'];
        // Attempt to execute the prepared statement
        if (!mysqli_stmt_execute($stmt2)) {
            $error = mysqli_error($link);
            echo "Something went wrong. Please try again later. $error";
        }
        mysqli_stmt_close($stmt2);
    }
}

if (isset($_SESSION['username'])) {
    refreshSeenTime($link);
    echo '<script src="https://cdn.jsdelivr.net/npm/socket.io-client@2/dist/socket.io.js"></script>';
}


function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$blacklistsql = 'SELECT * FROM loginattempts WHERE ipaddress = ?';
if ($blackliststmt = mysqli_prepare($link, $blacklistsql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($blackliststmt, 's', $param_ipaddress);

    // Set parameters
    $param_ipaddress = $_SERVER['REMOTE_ADDR'];

    // Attempt to execute the prepared statement
    if (mysqli_stmt_execute($blackliststmt)) {
        /* store result */
        mysqli_stmt_store_result($blackliststmt);

        if (mysqli_stmt_num_rows($blackliststmt) >= 3) {
            die('Blacklisted IP address');
        }
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}