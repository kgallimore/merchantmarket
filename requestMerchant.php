<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'user') {
    $list = $_POST['list'];
    foreach ($_POST as $key => $arrayValue) {
        if ($key !== 'list' && in_array($key, $possibleContactOptionsMerch, true )) {
            if($key === 'email'){
                if(!filter_var($arrayValue, FILTER_VALIDATE_EMAIL)){
                    die('Invalid email');
                }
                $emailConfirmation = filter_var($arrayValue, FILTER_SANITIZE_EMAIL);
                $contactArray['email'] = filter_var($arrayValue, FILTER_SANITIZE_EMAIL);
            }elseif ($key === 'website'){
                if(!filter_var($arrayValue, FILTER_VALIDATE_URL)){
                    die('Invalid website');
                }

                $contactArray['website'] = filter_var($arrayValue, FILTER_SANITIZE_URL);
            }else{
                $contactArray[$key] = $arrayValue;
            }
        }
    }

    if (isset($contactArray) && $contactArray !== null) {
        $contactSerialized = json_encode($contactArray);
        $sql = "UPDATE users SET request_merchant_application = ?, request_merchant_list = ?, role = 'rmerchant', contactinfo = ? WHERE username = ?";
    } else {
        $sql = "UPDATE users SET request_merchant_application = ?, request_merchant_list = ?, role = 'rmerchant' WHERE username = ?";
    }

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        if ($contactSerialized !== null) {
            mysqli_stmt_bind_param($stmt, 'ssss', $application, $list, $contactSerialized, $_SESSION['username']);
        } else {
            mysqli_stmt_bind_param($stmt, 'sss', $application, $list, $_SESSION['username']);
        }

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['role'] = 'rmerchant';
            mail(MAIL_TO,'New merchant request','A new user, ' . $_SESSION['username'] . ', is available for you to review. https://' . SITE_URL . '/approveMerchants.php','From: noreply@'. SITE_URL);
            if(isset($emailConfirmation)){
                mail($emailConfirmation,'Merchant Request Confirmation','Dear ' . $_SESSION['username'] . ', we have received your application. You will be contacted here upon approval.','From: noreply@' . SITE_URL);
            }
        } else {
            echo 'Something went wrong. Please try again later.';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}
?>
<html lang="en">

<script type="text/javascript" src="resources/js/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="resources/css/chosen.min.css">
<h2>Please note, there is only framework in place for services. The digital goods framework is a WIP.</h2>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <?php
    if ($_SESSION['role'] === 'rmerchant') {
        echo '<h2>Your application is processing. Please check back later.</h2>';
    } elseif ($_SESSION['role'] === 'merchant') {
        echo '<h2>You are already a merchant</h2>';
    } else if (detectMobile()) {
        echo '<h3>Please sign up on a computer to be able to add contact options</h3> Or "Request Desktop Site"';
    } else {
        echo "<h3>Contact information, for the reviewer and your merchant page</h3><h4>You may be contacted at one of these at the admin's discretion upon approval</h4><h5>You WILL be contacted by email automatically if you provide one.</h5>
<label>
<div style=\"text-align:left;\">
<select class=\"chosen-select\" multiple>
    <option value=''></option>
    <optgroup label=\"DISCORD\">
    <option value='discord_server'>Discord Server Invite</option>
    <option value='discord_server_widget'>Discord Server Widget</option>
    <option value='discord_user'>Discord User</option>
    </optgroup>
    <optgroup label=\"TELEGRAM\">
    <option value='telegram_group'>Telegram Group</option>
    <option value='telegram_user'>Telegram User</option>
    </optgroup>
    <optgroup label=\"SKYPE\">
    <option value='skype_chat'>On page chat</option>
    <option value='skype'>Skype</option>
    </optgroup>
    <option value='email'>Email</option>
    <option value='website'>Website</option>
    <option value='other'>Other</option>
</select>
</div>
</label>
<div id='contact_options'></div>
<h3>List or link to services. Data will be pulled   to populate your account. This is a manual process so it may take time. Please make sure you include prices, service types, and service names. Notes for each service are optional.</h3><label>
    <textarea name=\"list\" cols=\"150\" rows=\"15\" required placeholder=\"A link or list of services\"></textarea>
</label><br><br><button class=\"btn btn-primary\"  id='submit' type=\"submit\">Submit</button>";
    }
    ?>
</form>
<br>
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
        const discWidget = $("option:contains('Discord Server Widget')");
        const discUser = $("option:contains('Discord User')");
        const discInvite = $("option:contains('Discord Server Invite')");
        if (params.deselected !== undefined) {
            switch (params.deselected) {
                case "discord_server":
                    discWidget.attr("disabled", false);
                    discUser.attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case "discord_server_widget":
                    discInvite.attr("disabled", false);
                    discUser.attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case 'discord_user':
                    discWidget.attr("disabled", false);
                    discInvite.attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case 'telegram_group':
                    $("option:contains('Telegram User')").attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case 'telegram_user':
                    $("option:contains('Telegram Group')").attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case 'skype_chat':
                    $("option:contains('Skype')").attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
                case 'skype':
                    $("option:contains('On page chat')").attr("disabled", false);
                    chosenForm.trigger("chosen:updated");
                    break;
            }
            $('#' + deselectedOption).remove();
        } else {
            switch (params.selected) {
                case "discord_server":
                    discWidget.attr("disabled", true);
                    discUser.attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='discord_server'><input type='text' name='discord_server' placeholder='Discord Server invite url' pattern='(^https:\\/\\/discord.gg\\/[a-zA-z1-9]{6}|[a-zA-z1-9]{6})+$' title='Entire url or just the 6 digit code'><br></div>");
                    break;
                case "discord_server_widget":
                    discInvite.attr("disabled", true);
                    discUser.attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    alert("Please make sure you also enable the widget in your server settings!");
                    $div.append("<div id='discord_server_widget'><input type='text' name='discord_server_widget' placeholder='Discord Server ID' pattern='[0-9]{18}' title='18 digit number found under server settings -> widget'><br></div>");
                    break;
                case 'discord_user':
                    discWidget.attr("disabled", true);
                    discInvite.attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='discord_user'><input type='text' name='discord_user' placeholder='Discord user' title='Entire username and tag' pattern='[^@:#(```)]{2,32}#[0-9]{4}'><br></div>");
                    break;
                case 'telegram_group':
                    $("option:contains('Telegram User')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='telegram_group'><input type='text' name='telegram_group' placeholder='Telegram Group Invite' title='Entire url' pattern='(https:\\/\\/t.me\\/joinchat\\/)[a-zA-Z0-9]{24,}'><br></div>");
                    break;
                case 'telegram_user':
                    $("option:contains('Telegram Group')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='telegram_user'><input type='text' name='telegram_user' placeholder='Telegram Username'><br></div>");
                    break;
                case 'skype_chat':
                    $("option:contains('Skype')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='skype_chat'><input type='text' name='skype_chat' placeholder='Skype'><br></div>");
                    break;
                case 'skype':
                    $("option:contains('On page chat')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='skype'><input type='text' name='skype' placeholder='Skype'><br></div>");
                    break;
                case 'email':
                    $div.append("<div id='email'><input type='email' name='email' placeholder='Email'><br></div>");
                    break;
                case 'website':
                    $div.append("<div id='website'><input type='text' name='website' placeholder='Website'><br></div>");
                    break;
                case 'other':
                    $div.append("<div id='other'><input type='text' name='other' placeholder='Other contact info'><br></div>");
                    break;
            }
        }
    });

</script>
<?php
include_once 'footer.php';
