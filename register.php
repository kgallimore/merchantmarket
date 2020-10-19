<?php

require_once 'config/config.php';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('location: index.php');
    exit;
}


function getUserIpAddr()
{
    /*if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{*/
    //}
    return $_SERVER['REMOTE_ADDR'];
}

// Define variables and initialize with empty values
$username = $password = $confirm_password = '';
$username_err = $password_err = $confirm_password_err = $captcha_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate username
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        // Prepare a select statement
        $sql = 'SELECT id FROM users WHERE username like ?';

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 's', $param_username);

            // Set parameters
            $param_username = trim($_POST['username']);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {
                    $username_err = 'This username is already taken.';
                } else {
                    $username = trim($_POST['username']);
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Validate password
    if (empty(trim($_POST['password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password must have at least 6 characters.';
    } else {
        $password = trim($_POST['password']);
    }

    // Validate confirm password
    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = 'Please confirm password.';
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password !== $confirm_password)) {
            $confirm_password_err = 'Password did not match.';
        }
    }

    $whitelist = array(
        '127.0.0.1',
        '::1'
    );

    if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist, true)) {
        if (empty(trim($_POST['g-recaptcha-response']))) {
            $captcha_err = 'Please check the captcha.';
        } else {
            $captcha = $_POST['g-recaptcha-response'];
            $secretKey = '6LeeB7EUAAAAAI5NdWlunoxU94A2UPz35YwS1RbS';
            // post request to server
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) . '&response=' . urlencode($captcha);
            $response = file_get_contents($url);
            $responseKeys = json_decode($response, true);
            // should return JSON with success as true
            if (!$responseKeys['success']) {
                $captcha_err = 'Failed captcha check';
            }
        }
    }
    // Prepare a select statement

    $ipsql = 'SELECT id FROM users WHERE ipaddress = ?';

    if ($ipstmt = mysqli_prepare($link, $ipsql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($ipstmt, 's', $param_ipaddress);
        $param_ipaddress = getUserIpAddr();
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($ipstmt)) {
            /* store result */

            mysqli_stmt_store_result($ipstmt);

            if (mysqli_stmt_num_rows($ipstmt) === 1) {
                echo 'Ip address is already in use';
                $ipaddr_err = 'This ip is already taken.';
            } else {
                $ipaddress = getUserIpAddr();
            }
        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }


        // Close statement
        mysqli_stmt_close($ipstmt);
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($ipaddr_err) && empty($password_err) && empty($confirm_password_err) && empty($captcha_err)) {
        foreach ($_POST as $key => $arrayValue) {
            if ($key !== 'username' && $key !== 'password' && in_array($key, $possibleContactOptions, true)) {
                if($key === 'email'){
                    filter_var($arrayValue, FILTER_SANITIZE_EMAIL);
                }
                $contactArray[$key] = $arrayValue;
            }
        }
        // Prepare an insert statement
        if (isset($contactArray) && $contactArray !== null) {
            $contactSerialized = json_encode($contactArray);
            $sql = 'INSERT INTO users (username, password, ipaddress, contactinfo) VALUES (?, ?, ?, ?)';
        }else{
            $sql = 'INSERT INTO users (username, password, ipaddress) VALUES (?, ?, ?)';
        }
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            if (isset($contactSerialized) && $contactSerialized !== null) {
                mysqli_stmt_bind_param($stmt, 'ssss', $param_username, $param_password, $ipaddress, $contactSerialized);
            }else{
                mysqli_stmt_bind_param($stmt, 'sss', $param_username, $param_password, $ipaddress);
            }

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header('location: login.php');
            } else {
                echo 'Something went wrong. Please try again later.';
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script type="text/javascript" src="resources/js/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="resources/css/chosen.min.css">
    <style type="text/css">
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="wrapper" align="center">
    <h2>Sign Up</h2>
    <p>Please fill this form to create an account.</p>
    <p> Your IP during creation will be stored. There is a 1 account per IP limit</p>
    <?php if (detectMobile()){
        echo '<h3>Please sign up on a computer to be able to add contact options</h3> 
Or "Request Desktop Site"';
    }else{
        echo 'Contact options(Currently may not be changed or added to later):
    <label>
        <div style="text-align:left;">
            <select class="chosen-select" multiple>
                <option value=\'\'></option>
                <option value=\'discord_user\'>Discord User</option>
                <option value=\'telegram_user\'>Telegram User</option>
                <option value=\'skype\'>Skype</option>
                <option value=\'email\'>Email</option>
                <option value=\'other\'>Other</option>
            </select>
        </div>
    </label>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
            <label>Username</label>
            <label>
                <input type="text" name="username" class="form-control" pattern="[a-zA-Z0-9_.-$]+"
                       title="Up to 16 letters, numbers, $ . _ or - only"
                       maxlength="16" value="<?php echo $username; ?>">
            </label>
            <span class="help-block"><?php echo $username_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
            <label>Password</label>
            <label>
                <input type="password" name="password" minlength="8" class="form-control"
                       pattern="[\\\-\/a-zA-Z0-9._ +=()*&^%$,?<>!@#~`|]+"
                       title="8 characters minimum" value="<?php echo $password; ?>">
            </label>
            <span class="help-block"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
            <label>Confirm Password</label>
            <label>
                <input type="password" name="confirm_password" minlength="8" class="form-control"
                       pattern="[\\\-\/a-zA-Z0-9._ +=()*&^%$,?<>!@#~`|]+" value="<?php echo $confirm_password; ?>">
            </label>
            <span class="help-block"><?php echo $confirm_password_err; ?></span>
        </div>
        <div id="contact_options" class="form-group">

        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Submit">
            <input type="reset" class="btn btn-default" value="Reset">
        </div>
        <div class="g-recaptcha" data-sitekey="6LeeB7EUAAAAACh6oa9tdNTJ5-ktdBYQuWKXANlP"></div>
        <span class="help-block"><?php echo $captcha_err; ?></span>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </form>
</div>
</body>
<script>
    const chosenForm = $('.chosen-select');
    $(document).ready(
        chosenForm.chosen({
            search_contains: true,
            width: '150px'
        })
    );
    chosenForm.on('change', function (evt, params) {
        const $div = $("#contact_options");
        const deselectedOption = params.deselected;
        if (deselectedOption !== undefined) {
            $('#' + deselectedOption).remove();
        } else {
            switch (params.selected) {
                case 'discord_user':
                    $div.append("<div id='discord_user'><input type='text' maxlength='32' name='discord_user' placeholder='Discord user' title='Entire username and tag' pattern='[^@:#(```)]{2,32}#[0-9]{4}'><br></div>");
                    break;
                case 'telegram_user':
                    $div.append("<div id='telegram_user'><input type='text' maxlength='32' name='telegram_user' placeholder='Telegram Username'><br></div>");
                    break;
                case 'skype':
                    $div.append("<div id='skype'><input type='text' name='skype' maxlength='32' placeholder='Skype'><br></div>");
                    break;
                case 'email':
                    $div.append("<div id='email'><input type='email' name='email' maxlength='50' placeholder='Email'><br></div>");
                    break;
                case 'other':
                    $div.append("<div id='other'><input type='text' maxlength='50' name='other' placeholder='Other contact info'><br></div>");
                    break;
            }
        }
    });

</script>
<?php
include_once 'footer.php';
