<?php
require_once 'header.php';
if ($_SESSION['role'] !== 'merchant' && $_SESSION['role'] !== 'admin') {
    header('location: index.php');
    exit;
}
?>
<style>
    .tooltip {
        position: relative;
        display: inline-block;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 140px;
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 150%;
        left: 50%;
        margin-left: -75px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #555 transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    #news {
        display: none;
    }

    #contactOptions {
        display: none;
    }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
<script type="text/javascript" src="resources/js/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="resources/css/chosen.min.css">
<script src="resources/js/tinymce/tinymce.min.js"></script>
<script src="resources/js/tinymce/jquery.tinymce.min.js"></script>
<p>
    <button onclick="toggleServices()" id="servicesToggle" class="btn" disabled>Manage Services</button>
    <button onclick="toggleNews()" id="newsToggle" class="btn">Manage News</button>
    <button onclick="toggleContact()" id="contactToggle" class="btn">Manage Contact Methods</button>
</p>

<div id="services">
    <h2>Current Services:</h2>
    <table align="center">
        <thead>
        <tr>
            <?php
            if ($_SESSION['role'] === 'admin') {
                echo "<th class='sort' data-sort='merchantName'>Merchant Name</th>";
            }
            ?>
            <th class="sort" data-sort="serviceType">Service Type</th>
            <th class="sort" data-sort="serviceName">Service Name</th>
            <th class="sort" data-sort="cost">Cost</th>
            <th class="sort" data-sort="countries">Countries</th>
            <th>Notes</th>
            <th colspan="2">
                <input type="text" class="search" placeholder="Search services"/>
            </th>
        </tr>
        </thead>
        <tbody class="list">
        <?php
        if ($_SESSION['role'] === 'admin') {
            $sql = 'SELECT id, serviceType, serviceName, cost, notes, user, countries FROM services';
        } else {
            $sql = 'SELECT id, serviceType, serviceName, cost, notes, countries FROM services WHERE user = ?';
        }


        if ($stmt = mysqli_prepare($link, $sql)) {
            if ($_SESSION['role'] !== 'admin') {
                mysqli_stmt_bind_param($stmt, 's', $param_username);
            }

            $param_username = $_SESSION['username'];
            if (mysqli_stmt_execute($stmt)) {
                if ($_SESSION['role'] === 'admin') {
                    mysqli_stmt_bind_result($stmt, $serviceID, $type1, $name1, $cost1, $notes1, $user1, $countries1);
                } else {
                    mysqli_stmt_bind_result($stmt, $serviceID, $type1, $name1, $cost1, $notes1, $countries1);
                }


                while (mysqli_stmt_fetch($stmt)) {
                    $countriesSub = substr($countries1, 0, -1);
                    Echo "<tr><td class=\"id\" style=\"display:none;\">$serviceID</td>";
                    if ($_SESSION['role'] === 'admin') {
                        echo "<td class='merchantName'>$user1</td>";
                    }
                    echo "<td class='serviceType'>$type1</td>";
                    echo "<td class='serviceName'><form action=\"/serviceEdit.php?serviceID=$serviceID\" method=\"post\"><input type='submit' value='$name1'><input type=\"text\" name=\"serviceID\" value='$serviceID' style='display: none'></form></td>";
                    echo "<td class='cost'>$cost1</td>";
                    echo "<td class='cost'>$countriesSub</td>";
                    echo "<td class='notes'>$notes1</td>";
                    echo "<td class='edit'><button class='edit-item-btn'>Edit</button></td>";
                    echo "<td class='remove'><button class='remove-item-btn'>Remove</button></td></tr>";
                }


            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
        }
        mysqli_stmt_close($stmt);


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['updatedNews']) && strlen($_POST['updatedNews']) < 4000) {
                $updatedNews = $_POST['updatedNews'];
                $sql = 'UPDATE users SET news = ? WHERE username = ?';
                if ($stmt = mysqli_prepare($link, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, 'ss', $updatedNews, $_SESSION['username']);
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                    } else {
                        echo 'Something went wrong. Please try again later.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo 'Oops! Something went wrong. Please try again later.';
                }
            }
            elseif (isset($_POST['serviceName'], $_POST['cost'])) {
                // Validate username
                if (empty(trim($_POST['serviceName']))) {
                    $serviceName_err = 'Please enter a service name.';
                } else {
                    $serviceName = trim($_POST['serviceName']);
                }

                if (empty(($_POST['countries']))) {
                    $countries_err = 'Please enter countries.';
                } else {
                    $countries = '';
                    foreach ($_POST['countries'] as $addCountry) {
                        $countries .= (string)$addCountry;
                    }
                }

                if (empty(trim($_POST['serviceType']))) {
                    $serviceType_err = 'Please enter a service name.';
                } else {
                    // Prepare a select statement
                    $sql = 'SELECT services FROM users WHERE username = ?';
                    $serviceType = trim($_POST['serviceType']);
                    if ($stmt = mysqli_prepare($link, $sql)) {
                        // Bind variables to the prepared statement as parameters
                        mysqli_stmt_bind_param($stmt, 's', $_SESSION['username']);

                        // Attempt to execute the prepared statement
                        if (mysqli_stmt_execute($stmt)) {
                            //store result
                            mysqli_stmt_bind_result($stmt, $currentServicesBind);
                            mysqli_stmt_fetch($stmt);

                            $currentServices = explode(',', $currentServicesBind);
                            if (!in_array($serviceType, $currentServices, true)) {

                                if ($currentServices[0] !== '') {

                                    $currentServices[] = trim($_POST['serviceType']);
                                    $updatedServices = implode(',', $currentServices);
                                } else {
                                    $updatedServices = $serviceType;
                                }
                                $sql2 = 'UPDATE users SET services = ? WHERE username = ?';
                                $link2 = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                                if ($stmt2 = mysqli_prepare($link2, $sql2)) {
                                    // Bind variables to the prepared statement as parameters
                                    mysqli_stmt_bind_param($stmt2, 'ss', $updatedServices, $_SESSION['username']);
                                    // Attempt to execute the prepared statement
                                    if (!mysqli_stmt_execute($stmt2)) {
                                        echo 'Something went wrong. Please try again later.';

                                    }
                                    mysqli_stmt_close($stmt2);
                                } else {
                                    echo 'Oops! Something went wrong. Please try again later.';
                                }
                                mysqli_close($link2);
                            } else {
                                echo 'Oops! Something went wrong. Please try again later.';
                            }

                        } else {
                            echo 'Oops! Something went wrong. Please try again later.';
                        }
                    }

                    // Close statement
                    mysqli_stmt_close($stmt);
                }

                // Validate password
                if (empty(trim($_POST['cost']))) {
                    $cost_err = 'Please enter a cost.';
                } else {
                    $cost = trim($_POST['cost']);
                }
                if (empty(trim($_POST['notes']))) {
                    $notes_err = 'Please enter notes.';
                } else {
                    $notes = trim($_POST['notes']);
                }

                // Check input errors before inserting in database
                if (empty($serviceName_err) && empty($cost_err) && empty($notes_err) && empty($serviceType_err)) {

                    // Prepare an insert statement
                    if (!empty(trim($_POST['id']))) {
                        $id = $_POST['id'];
                        // Prepare a select statement
                        $sql = 'SELECT ?, id FROM services WHERE user = ?';

                        if ($stmt = mysqli_prepare($link, $sql)) {
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, 'ss', $param_serviceName, $_SESSION['username']);

                            // Set parameters
                            $param_serviceName = trim($_POST['serviceName']);

                            // Attempt to execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                /* store result */
                                mysqli_stmt_bind_result($stmt, $result);
                                mysqli_stmt_fetch($stmt);

                                if (mysqli_stmt_num_rows($stmt) === 1) {
                                    $serviceName_err = 'This service name is already taken.';
                                } else {
                                    $serviceName = trim($_POST['serviceName']);
                                }
                            } else {
                                echo 'Oops! Something went wrong. Please try again later.';
                            }
                        }

                        // Close statement
                        mysqli_stmt_close($stmt);
                        $sql = 'UPDATE services SET serviceType = ?, serviceName = ?, cost = ?, notes = ? WHERE id = ?';
                        if ($stmt = mysqli_prepare($link, $sql)) {
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, 'sssss', $serviceType, $serviceName, $cost, $notes, $id);


                            // Attempt to execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<script>window.location.reload();</script>';
                            } else {
                                echo 'Something went wrong. Please try again later.';
                            }
                        }
                        // Close statement
                        mysqli_stmt_close($stmt);
                    } else {

                        $sql = 'INSERT INTO services (serviceType, serviceName, user, cost, notes, countries, userID) VALUES (?, ?, ?, ?, ?, ?, ?)';
                        if ($stmt = mysqli_prepare($link, $sql)) {
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, 'ssssssi', $serviceType, $serviceName, $_SESSION['username'], $cost, $notes, $countries, $_SESSION['id']);
                            $serviceType = trim($_POST['serviceType']);

                            // Attempt to execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<script>window.location.reload();</script>';
                            } else {
                                echo 'Something went wrong. Please try again later.';
                            }
                        }
                        // Close statement
                        mysqli_stmt_close($stmt);
                    }
                }
                // Close connection

            }
            else {
                foreach ($_POST as $key => $arrayValue) {
                    if($key === 'email'){
                        filter_var($arrayValue, FILTER_SANITIZE_EMAIL);
                    }
                    if($key === 'website'){
                        filter_var($arrayValue, FILTER_SANITIZE_URL);
                    }
                    $contactArray[$key] = $arrayValue;
                }
                $contactSerialized = json_encode($contactArray);
                $sql = 'UPDATE users SET contactinfo = ? WHERE username = ?';
                if ($stmt = mysqli_prepare($link, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, 'ss', $contactSerialized, $_SESSION['username']);
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                    } else {
                        echo 'Something went wrong. Please try again later.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo 'Oops! Something went wrong. Please try again later.';
                }
            }
        }

        ?>
        </tbody>
    </table>
    <p>&nbsp;</p>
    <div id="singleTableDiv">
        <form id="singleTableForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <table align="center" id="singleTable">
                <td class="serviceType">
                    <input type="hidden" name="id" id="id-field"/>
                    <input list="serviceTypes" id="serviceType-field" placeholder="i" name="serviceType"
                           value="<?php if (isset($serviceType)) {
                               echo htmlspecialchars($serviceType);
                           } ?>">
                    <datalist id="serviceTypes">
                        <?php
                        $sql = 'SELECT services, news, contactinfo FROM users WHERE username = ?';

                        if ($stmt = mysqli_prepare($link, $sql)) {
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, 's', $_SESSION['username']);

                            // Attempt to execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                /* store result */
                                mysqli_stmt_bind_result($stmt, $currentServicesFetch, $currentNewsFetch, $currentContactFetch);
                                if (mysqli_stmt_fetch($stmt)) {
                                    if ($currentContactFetch !== null) {
                                        $unserializedContact = json_decode($currentContactFetch, true);
                                    }
                                    $currentServicesArray = explode(',', $currentServicesFetch);
                                    foreach ($currentServicesArray as $option) {
                                        echo htmlspecialchars("<option value=\"$option\">");
                                    }
                                }


                            } else {
                                echo 'Oops! Something went wrong. Please try again later.';
                            }
                            mysqli_stmt_close($stmt);
                        }
                        ?>
                    </datalist>
                    <span class="help-block"><?php if (isset($serviceType_err)) {
                            echo $serviceType_err;
                        } ?></span>
                </td>
                <td>
                    <input type="text" id="serviceName-field" name="serviceName" pattern="[-a-zA-Z0-9.$_ ]+"
                           title="Up to 16 letters, numbers, $ . _ or - only"
                           maxlength="32" placeholder="Service Name"
                           value="<?php if (isset($serviceName)) {
                               echo htmlspecialchars($serviceName);
                           } ?>"/>
                    <span class="help-block"><?php if (isset($serviceName_err)) {
                            echo $serviceName_err;
                        } ?></span>
                </td>
                <td>
                    <input type="text" id="cost-field" name="cost" pattern="[-0-9.$%]+"
                           title="Numbers, - . % and $ only"
                           maxlength="12" placeholder="Cost" value="<?php if (isset($cost)) {
                        echo $cost;
                    } ?>"/>
                    <span class="help-block"><?php if (isset($cost_err)) {
                            echo $cost_err;
                        } ?></span>
                </td>
                <td>
                    <label for="notes-field"></label><input type="text" id="notes-field" size="55" name="notes" maxlength="255" placeholder="Notes"/>
                    <span class="help-block"><?php if (isset($notes_err)) {
                            echo $notes_err;
                        } ?></span>
                </td>
                <td>
                    <label>
                        <input type="checkbox" name="countries[]" value="USA/">
                    </label> USA<br>
                    <label>
                        <input type="checkbox" name="countries[]" value="CAN/">
                    </label> CAN<br>
                    <label>
                        <input type="checkbox" name="countries[]" value="EUR/">
                    </label> EUR<br>
                    <label>
                        <input type="checkbox" name="countries[]" value="ALL/">
                    </label> ALL
                    <span class="help-block"><?php if (isset($countries_err)) {
                            echo $countries_err;
                        } ?></span>
                </td>
                <td class="add">
                    <div class="form-group">
                        <input type="submit" id="editSubmitBtn" class="btn btn-primary" value="Add">
                        <input type="reset" onclick="document.getElementById('editSubmitBtn').value = 'Add';"
                               class="btn btn-default" value="Reset">
                    </div>
                </td>
            </table>

        </form>
    </div>

    <div id="manyTableDiv" style="display: none">
        <form id="manyTableForm" method="post" action="multiServiceAdd.php">
            <table align="center" id="manyTable">
                <?php
                if ($_SESSION['role'] === 'admin') {
                    echo '<td><input list="userName" id="userName-field" placeholder="Username" name="userName"><datalist id="userName">';
                    $sql = "SELECT username FROM users WHERE role = 'merchant'";

                    if ($stmt = mysqli_prepare($link, $sql)) {
                        // Attempt to execute the prepared statement
                        if (mysqli_stmt_execute($stmt)) {
                            /* store result */
                            mysqli_stmt_bind_result($stmt, $username);
                            while (mysqli_stmt_fetch($stmt)) {
                                echo htmlspecialchars("<option value=\"$username\">");
                            }


                        } else {
                            echo 'Oops! Something went wrong. Please try again later.';
                        }
                    }
                    mysqli_stmt_close($stmt);
                    echo '</datalist></td>';
                    echo "<td><input name='serviceType' id='serviceType-field' placeholder='Service Type'></td>";
                } else {
                    echo '<td><input list="serviceTypes" id="serviceType-field" placeholder="Service Type" name="serviceType"><datalist id="serviceTypes">';
                    foreach ($currentServicesArray as $option) {
                        echo "<option value=\"$option\">";
                    }
                    echo '</datalist></td>';
                }
                ?>
                <td>
                    <label>
                        <input type="checkbox" name="countries2[]" value="USA/">
                    </label> USA<br>
                    <label>
                        <input type="checkbox" name="countries2[]" value="CAN/">
                    </label> CAN<br>
                    <label>
                        <input type="checkbox" name="countries2[]" value="EUR/">
                    </label> EUR<br>
                    <label>
                        <input type="checkbox" name="countries2[]" value="ALL/">
                    </label> ALL
                </td>
                <td>
                    <label>
                        <textarea name="serviceNames" rows="15" placeholder="Service Names"></textarea>
                    </label>
                </td>
                <td>
                    <label>
                        <textarea name="costs" rows="15" placeholder="Costs"></textarea>
                    </label>
                </td>
                <td>
                    <label>
                        <textarea name="notes" rows="15" placeholder="Notes"></textarea>
                    </label>
                </td>
            </table>
            <input type="submit" id="editSubmitBtn" class="btn btn-primary" value="Add Many">
        </form>
    </div>
    <p>
        <button onclick="toggleTable()" id="manyTableToggle" class="btn">Toggle Multiple Input</button>
    </p>
    </p></div>
<div id="news">
    <?php
    echo '<h2>Current news:</h2><br>';
    echo $currentNewsFetch;
    ?>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <h2>Update </p>news:</h2>
    <form id="updateNews" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label>
            <textarea name="updatedNews" class="updatedNews" placeholder="Update News"></textarea>
        </label>
    </form>

</div>
<div id="contactOptions">
    <?php
    $afterContacts = '';
    foreach ($unserializedContact as $key => $arrayValue) {
        if ($key === 'email') {
            echo "<a href=\"mailto:$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
        } elseif (in_array($key, ['discord_server', 'telegram_group', 'website'])) {
            echo "<a href=\"$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
        } elseif ($key === 'discord_server_widget') {
            $afterContacts .= "Discord widget id:$arrayValue";
        } elseif ($key === 'skype_chat') {
            $afterContacts .= "Skype chat user id: $arrayValue";
        } elseif (($key === 'other')) {
            if (strpos($arrayValue, '<') === false) {
                $otherContact = $arrayValue;
            }
        } else {
            $safeValue = htmlspecialchars($arrayValue);
            echo "<input type=\"text\" style='display: none' value=\"$arrayValue\" id=\"$key$safeValue\">";
            echo "<img border=\"0\" alt=\"$arrayValue\" src=\"resources/img/$key.png\" width=\"50\" height=\"50\" onclick=\"copyClipboard('$key$safeValue')\">";
        }
    }
    if ($otherContact !== '') {
        echo '<br>' . $afterContacts;
    }
    if (isset($otherContact)) {
        echo "<br>Other contact option: $otherContact";
    }
    echo '<div>Icons made by <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/"         title="Flaticon">www.flaticon.com</a></div>';

    ?>
    <h3>Contact information</h3>
    <label style="text-align: left;">
        <select class="chosen-select" multiple>
            <option value=''></option>
            <optgroup label="DISCORD">
                <option value='discord_server' <?php if (isset($unserializedContact['discord_server_widget']) || isset($unserializedContact['discord_user'])) {
                    echo 'disabled';
                }
                if (isset($unserializedContact['discord_server'])) {
                    echo ' selected';
                } ?>>Discord Server Invite
                </option>
                <option value='discord_server_widget' <?php if (isset($unserializedContact['discord_server']) || isset($unserializedContact['discord_user'])) {
                    echo 'disabled';
                }
                if (isset($unserializedContact['discord_server_widget'])) {
                    echo ' selected';
                } ?>>Discord Server Widget
                </option>
                <option value='discord_user' <?php if (isset($unserializedContact['discord_server']) || isset($unserializedContact['discord_server_widget'])) {
                    echo 'disabled';
                }
                if (isset($unserializedContact['discord_user'])) {
                    echo ' selected';
                } ?>>Discord User
                </option>
            </optgroup>
            <optgroup label="TELEGRAM">
                <option value='telegram_group' <?php if (isset($unserializedContact['telegram_user'])) {
                    echo 'disabled';
                } elseif (isset($unserializedContact['telegram_group'])) {
                    echo 'selected'; } ?>>
                    Telegram Group
                </option>
                <option value='telegram_user' <?php if (isset($unserializedContact['telegram_group'])) {
                    echo 'disabled';
                } elseif (isset($unserializedContact['telegram_user'])) {
                    echo 'selected'; } ?>>
                    Telegram User
                </option>
            </optgroup>
            <optgroup label="SKYPE">
                <option value='skype_chat' <?php if (isset($unserializedContact['skype'])) {
                    echo 'disabled';
                } elseif (isset($unserializedContact['skype_chat'])) {
                    echo 'selected'; } ?>>
                    On page chat
                </option>
                <option value='skype' <?php if (isset($unserializedContact['skype_chat'])) {
                    echo 'disabled';
                } elseif (isset($unserializedContact['skype'])) {
                    echo 'selected'; } ?>>
                    Skype
                </option>
            </optgroup>
            <option value='email' <?php if (isset($unserializedContact['email'])) {
                echo 'selected';
            } ?>>Email</option>
            <option value='website' <?php if (isset($unserializedContact['website'])) {
                echo 'selected';
            } ?>>Website
            </option>
            <option value='other' <?php if (isset($unserializedContact['other'])) {
                echo 'selected';
            } ?>>Other</option>
        </select>
    </label>
    <form id="contact_options" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <button type="submit">Submit</button>
        <br>
        <?php
        foreach ($unserializedContact as $key => $arrayValue) {
            $safeValue = htmlspecialchars($arrayValue);
            switch ($key) {
                case 'discord_server':
                    echo "<div id='discord_server'>Discord Server<br><input type='text' name='discord_server' placeholder='Discord Server invite url' pattern='(^https:\\/\\/discord.gg\\/[a-zA-z1-9]{6}|[a-zA-z1-9]{6})+$' title='Entire url or just the 6 digit code' value=\"$safeValue\"><br></div>";
                    break;
                case 'discord_server_widget':
                    echo "<div id='discord_server_widget'>Discord Server Widget<br><input type='text' name='discord_server_widget' placeholder='Discord Server ID' pattern='[0-9]{18}' title='18 digit number found under server settings -> widget' value=\"$safeValue\"><br></div>";
                    break;
                case 'discord_user':
                    echo "<div id='discord_user'>Discord Username<br><input type='text' name='discord_user' placeholder='Discord user' title='Entire username and tag' pattern='[^@:#(```)]{2,32}#[0-9]{4}' value='$safeValue'><br></div>";
                    break;
                case 'telegram_group':
                    echo "<div id='telegram_group'>Telegram Group<br><input type='text' name='telegram_group' placeholder='Telegram Group Invite' title='Entire url' pattern='(https:\\/\\/t.me\\/joinchat\\/)[a-zA-Z0-9]{22}' value='$safeValue'><br></div>";
                    break;
                case 'telegram_user':
                    echo "<div id='telegram_user'>Telegram Username<br><input type='text' name='telegram_user' placeholder='Telegram Username' value='$safeValue'><br></div>";
                    break;
                case 'skype_chat':
                    echo "<div id='skype_chat'>Skype Chat<br><input type='text' name='skype_chat' placeholder='Skype' value='$safeValue'><br></div>";
                    break;
                case 'skype':
                    echo "<div id='skype'>Skype<br><input type='text' name='skype' placeholder='Skype' value='$safeValue'><br></div>";
                    break;
                case 'email':
                    echo "<div id='email'>Email<br><input type='email' name='email' placeholder='Email' value='$safeValue'><br></div>";
                    break;
                case 'website':
                    echo "<div id='website'>Website<br><input type='text' name='website' placeholder='Website' value='$safeValue'><br></div>";
                    break;
                case 'other':
                    echo "<div id='other'>Other<br><input type='text' name='other' placeholder='Other contact info' value='$safeValue'><br></div>";
                    break;
            }
        }
        ?>

    </form>
</div>

</body>
<script>

    tinymce.init({
        selector: 'textarea.updatedNews',
        theme: 'silver',
        plugins: "autolink spellchecker autoresize autosave emoticons link media paste preview save textpattern wordcount",
        menubar: "insert edit view",
        toolbar: "save restoredraft emoticons link media paste preview wordcount",
        link_assume_external_targets: true,
        media_live_embeds: true,
        spellchecker_rpc_url: 'spellchecker.php',
        mobile: {
            theme: 'mobile',
            plugins: ['autosave', 'lists', 'autolink'],
            toolbar: ['undo', 'bold', 'italic', 'styleselect']
        }
    });
    <?php
    if ($_SESSION['role'] === 'admin') {
        //echo "var options = {valueNames: [ 'id', 'merchantName' 'serviceType', 'serviceName', 'cost', 'notes' ]};";
        echo "var options = {valueNames: [ 'id', 'merchantName', 'serviceType', 'serviceName', 'cost', 'notes' ]};";
    } else {
        echo "var options = {valueNames: [ 'id', 'serviceType', 'serviceName', 'cost', 'notes' ]};";
    }
    ?>


    // Init list
    const servicesList = new List('services', options);

    let idField = $('#id-field'),
        serviceTypeField = $('#serviceType-field'),
        serviceNameField = $('#serviceName-field'),
        costField = $('#cost-field'),
        countriesField = $('#countries-field'),
        notesField = $('#notes-field'),
        addBtn = $('#add-btn'),
        editBtn = $('#edit-btn').hide(),
        removeBtns = $('.remove-item-btn'),
        editBtns = $('.edit-item-btn');

    // Sets callbacks to the buttons in the list
    refreshCallbacks();


    addBtn.click(function () {
        servicesList.add({
            serviceType: serviceTypeField.val(),
            serviceName: serviceNameField.val(),
            cost: costField.val(),
            countries: countriesField.val(),
            notes: notesField.val()
        });
        clearFields();
        refreshCallbacks();
    });

    editBtn.click(function () {
        console.log("hello");
        const item = servicesList.get('id', idField.val())[0];
        item.values({
            id: idField.val(),
            serviceType: serviceTypeField.val(),
            serviceName: serviceNameField.val().substr(74),
            cost: costField.val(),
            notes: notesField.val()
        });

        clearFields();
        if (serviceTypeField.val() === '') {

        }
        document.getElementById("editSubmitBtn").value = "Edit";
    });
    function decodeHTML(str) {
        return str.replace("&amp;", "&");
    }
    function refreshCallbacks() {
        // Needed to add new buttons to jQuery-extended object
        removeBtns = $(removeBtns.selector);
        editBtns = $(editBtns.selector);
        removeBtns.click(function () {
            const itemId = $(this).closest('tr').find('.id').text();
            const serviceType = $(this).closest('tr').find('.serviceType').text();
            servicesList.remove('id', itemId);
            $.post("removeService.php", {
                serviceType: serviceType,
                id: itemId,
                user: "<?php echo $_SESSION['username']; ?>"
            });
        });

        editBtns.click(function () {
            const itemId = $(this).closest('tr').find('.id').text();
            const itemValues = servicesList.get('id', itemId)[0].values();
            const nameField = decodeHTML(itemValues.serviceName);
            idField.val(itemValues.id);
            serviceTypeField.val(itemValues.serviceType);
            serviceNameField.val(nameField.substring(74, nameField.length - 78));
            costField.val(itemValues.cost);
            notesField.val(itemValues.notes);

            document.getElementById("editSubmitBtn").value = "Edit";
        });
    }

    function clearFields() {
        idField.val('');
        serviceTypeField.val('');
        serviceNameField.val('');
        costField.val('');
        notesField.val('');
    }

    function toggleTable() {
        const singleTable = document.getElementById("singleTableDiv");
        const manyTable = document.getElementById("manyTableDiv");
        if (singleTable.style.display !== "none") {
            document.getElementById("singleTableForm").reset();
            document.getElementById("manyTableForm").reset();
            singleTable.style.display = "none";
            manyTable.style.display = "block";
            document.getElementById("manyTableToggle").innerText = 'Toggle Single Input';

        } else {
            document.getElementById("singleTableForm").reset();
            document.getElementById("manyTableForm").reset();
            manyTable.style.display = "none";
            singleTable.style.display = "block";
            document.getElementById("manyTableToggle").innerText = 'Toggle Multiple Input';
        }
    }

    function toggleNews() {
        document.getElementById("singleTableForm").reset();
        document.getElementById("manyTableForm").reset();
        document.getElementById("news").style.display = "block";
        document.getElementById("services").style.display = "none";
        document.getElementById("contactOptions").style.display = "none";
        document.getElementById("newsToggle").disabled = true;
        document.getElementById("contactToggle").disabled = false;
        document.getElementById("servicesToggle").disabled = false;
    }

    function toggleServices() {
        document.getElementById("updateNews").reset();
        document.getElementById("news").style.display = "none";
        document.getElementById("contactOptions").style.display = "none";
        document.getElementById("services").style.display = "block";
        document.getElementById("newsToggle").disabled = false;
        document.getElementById("contactToggle").disabled = false;
        document.getElementById("servicesToggle").disabled = true;
    }

    function toggleContact() {
        document.getElementById("updateNews").reset();
        document.getElementById("singleTableForm").reset();
        document.getElementById("manyTableForm").reset();
        document.getElementById("news").style.display = "none";
        document.getElementById("services").style.display = "none";
        document.getElementById("contactOptions").style.display = "block";
        document.getElementById("newsToggle").disabled = false;
        document.getElementById("contactToggle").disabled = true;
        document.getElementById("servicesToggle").disabled = false;
    }

    refreshCallbacks();
</script>
<script>
    function copyClipboard(toCopy) {
        const copyText = document.getElementById(toCopy);
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

    }

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
                    $div.append("<div id='discord_server'>Discord Server<br><input type='text' name='discord_server' placeholder='Discord Server invite url' pattern='(^https:\\/\\/discord.gg\\/[a-zA-z1-9]{6}|[a-zA-z1-9]{6})+$' title='Entire url or just the 6 digit code'><br></div>");
                    break;
                case "discord_server_widget":
                    discInvite.attr("disabled", true);
                    discUser.attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    alert("Please make sure you also enable the widget in your server settings!");
                    $div.append("<div id='discord_server_widget'>Discord Widget<br><input type='text' name='discord_server_widget' placeholder='Discord Server ID' pattern='[0-9]{18}' title='18 digit number found under server settings -> widget'><br></div>");
                    break;
                case 'discord_user':
                    discWidget.attr("disabled", true);
                    discInvite.attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='discord_user'>Discord Username<br><input type='text' name='discord_user' placeholder='Discord user' title='Entire username and tag' pattern='[^@:#(```)]{2,32}#[0-9]{4}'><br></div>");
                    break;
                case 'telegram_group':
                    $("option:contains('Telegram User')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='telegram_group'>Telegram Group<br><input type='text' name='telegram_group' placeholder='Telegram Group Invite' title='Entire url' pattern='(https:\\/\\/t.me\\/joinchat\\/)[a-zA-Z0-9]{22}'><br></div>");
                    break;
                case 'telegram_user':
                    $("option:contains('Telegram Group')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='telegram_user'>Telegram Username<br><input type='text' name='telegram_user' placeholder='Telegram Username'><br></div>");
                    break;
                case 'skype_chat':
                    $("option:contains('Skype')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='skype_chat'>Skype Chat<br><input type='text' name='skype_chat' placeholder='Skype'><br></div>");
                    break;
                case 'skype':
                    $("option:contains('On page chat')").attr("disabled", true);
                    chosenForm.trigger("chosen:updated");
                    $div.append("<div id='skype'>Skype<br><input type='text' name='skype' placeholder='Skype'><br></div>");
                    break;
                case 'email':
                    $div.append("<div id='email'>Email<br><input type='email' name='email' placeholder='Email'><br></div>");
                    break;
                case 'website':
                    $div.append("<div id='website'>Website<br><input type='text' name='website' placeholder='Website'><br></div>");
                    break;
                case 'other':
                    $div.append("<div id='other'>Other<br><input type='text' name='other' placeholder='Other contact info'><br></div>");
                    break;
            }
        }
    });

</script>
<?php
include_once 'footer.php';

