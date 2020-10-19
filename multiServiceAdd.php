<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = 'INSERT INTO services (merchantID, serviceType, serviceName, user, cost, notes, countries) VALUES (?, ?, ?, ?, ?, ?)';
    if (empty(($_POST['countries2']))) {
        $countries_err = 'Please enter countries.';
        exit;
    }

    $countries = '';
    foreach ($_POST['countries2'] as $addCountry) {
        $countries .= (string)$addCountry;
    }
    if (strpos($countries, 'ALL') !== false) {
        $countries = 'ALL/';
    }
    $user = trim($_POST['userName']);
    if ( $user === null || $user === '') {
        $user = $_SESSION['username'];
    }

    $serviceType = trim($_POST['serviceType']);
    $serviceNameArray = preg_split('/\r\n|\r|\n/', $_POST['serviceNames']);
    $serviceCostsArray = preg_split('/\r\n|\r|\n/', $_POST['costs']);
    $serviceNotesArray = preg_split('/\r\n|\r|\n/', $_POST['notes']);
    if (count($serviceNameArray) !== count($serviceCostsArray)) {
        if (count($serviceCostsArray) === 1) {
            if ($stmt = mysqli_prepare($link, $sql)) {
                $cost = $serviceCostsArray[0];
                mysqli_stmt_bind_param($stmt, 'issssss',$_SESSION['id'], $serviceType, $serviceName, $user, $cost, $notes, $countries);
                // Bind variables to the prepared statement as parameters
                for ($i = 0, $iMax = count($serviceNameArray); $i < $iMax; $i++) {
                    $serviceName = $serviceNameArray[$i];
                    if ($serviceNotesArray[$i] !== null) {
                        $notes = $serviceNotesArray[$i];
                    }
                    if (!mysqli_stmt_execute($stmt)) {
                        die('Something went wrong. Please try again later.');
                    }
                }
            }
            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            die('Costs and service names mismatch');
        }

    } else {
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "issssss",$_SESSION['id'], $serviceType, $serviceName, $user, $cost, $notes, $countries);
            // Bind variables to the prepared statement as parameters
            for ($i = 0, $iMax = count($serviceNameArray); $i < $iMax; $i++) {
                $serviceName = $serviceNameArray[$i];
                $cost = $serviceCostsArray[$i];
                if ($serviceNotesArray[$i] !== null) {
                    $notes = $serviceNotesArray[$i];
                }
                if (mysqli_stmt_execute($stmt)) {
                } else {
                    die('Something went wrong. Please try again later.');
                }
            }
        }
        // Close statement
        mysqli_stmt_close($stmt);
    }

    $sql = 'SELECT services FROM users WHERE id = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id']);

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            //store result
            mysqli_stmt_bind_result($stmt, $currentServicesBind);
            mysqli_stmt_fetch($stmt);
            $currentServices = explode(',', $currentServicesBind);
            if (!in_array($serviceType, $currentServices, true)) {
                $currentServices[] = trim($_POST['serviceType']);
                $updatedServices = implode(',', $currentServices);
                $sql2 = 'UPDATE users SET services = ? WHERE id = ?';
                $link2 = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                if ($stmt2 = mysqli_prepare($link2, $sql2)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt2, 'ss', $updatedServices, $_SESSION['id']);
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt2)) {
                    } else {
                        echo 'Something went wrong. Please try again later.';
                    }
                    mysqli_stmt_close($stmt2);
                } else {
                    echo 'Oops! Something went wrong 1. Please try again later.';
                }
                mysqli_close($link2);
            }
        } else {
            echo 'Oops! Something went wrong 2. Please try again later.';
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);

}