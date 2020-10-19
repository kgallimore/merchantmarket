<?php
require_once 'config/config.php';
$id = $_POST['id'];
$user = $_SESSION['username'];

$serviceType = $_POST['serviceType'];
$sql = 'DELETE FROM services WHERE id = ? and user = ?';
if ($stmt = mysqli_prepare($link, $sql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, 'ss', $id, $user);

    // Attempt to execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
    } else {
        echo 'Something went wrong. Please try again later.';
    }
}
// Close statement
mysqli_stmt_close($stmt);
$servicesArray = [];
$sql = 'SELECT serviceType FROM services WHERE user = ?';
if ($stmt = mysqli_prepare($link, $sql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, 's', $user);

    // Attempt to execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        /* store result */
        mysqli_stmt_bind_result($stmt, $serviceFetched);
        while (mysqli_stmt_fetch($stmt)) {
            if (!in_array($serviceFetched, $servicesArray, true)) {
                $servicesArray[] = $serviceFetched;
            }
        }
        if (!in_array($serviceType, $servicesArray, true)) {
            $sql2 = 'UPDATE users SET services = ? WHERE username = ?';
            $link2 = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if ($stmt2 = mysqli_prepare($link2, $sql2)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt2, "ss", $updatedServices, $_SESSION['username']);
                $updatedServices = implode(',', $servicesArray);
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt2)) {
                } else {
                    echo 'Something went wrong. Please try again later.';
                }
                mysqli_stmt_close($stmt2);
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
            mysqli_close($link2);
        }

    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}
// Close statement
mysqli_stmt_close($stmt);

