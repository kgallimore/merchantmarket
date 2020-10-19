<?php
include_once 'header.php';
$requestID = $_GET['requestID'];
$param_username = $_SESSION['username'];
$sql = 'SELECT customer, merchant, serviceName, last_see_merch, last_seen_cust, last_update, formdata, contactinfo, service_requests.merchantID, service_requests.status FROM service_requests INNER JOIN services ON service_requests.serviceID=services.id INNER JOIN users ON service_requests.customer=users.username WHERE requestID = ?';
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $requestID);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $customerName, $merchantName, $serviceName, $last_seen_merch, $last_seen_cust, $last_update, $formData, $customerContact, $merchantID, $status);
        if(mysqli_stmt_fetch($stmt)){
            mysqli_stmt_close($stmt);
            if($status !== 'Complete'){
                $formattedFormData = json_decode($formData);


            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST['action'] === 'completed'){
                    $sql2 = 'UPDATE service_requests SET last_update = unix_timestamp(), last_see_merch = unix_timestamp(), formdata = ?, status = ? where requestID = ? and merchantID = ?';
                    if ($stmt = mysqli_prepare($link, $sql2)) {
                        $new_form_Data = 'Service Completed';
                        $new_status = 'Complete';
                        mysqli_stmt_bind_param($stmt2, 'ssi', $new_form_Data,$new_status, $requestID, $_SESSION['id']);
                        if (mysqli_stmt_execute($stmt2)) {
                            mysqli_stmt_close($stmt2);
                        }

                    }

                }
                elseif ($_POST['action'] === 'update'){
                    $updateData = $_POST['updateData'];
                    $sql2 = 'INSERT INTO service_updates (requestID, userID, userName, userType, updateinfo) VALUES (?,?,?,?,?)';
                    if ($stmt2 = mysqli_prepare($link, $sql2)) {
                        mysqli_stmt_bind_param($stmt2, 'iisss', $requestID,  $_SESSION['id'], $param_username, $userType, $updateData);
                        if($_SESSION['id'] === $merchantID){
                            $userType = 'Merchant';
                        }
                        else{
                            $userType = 'Customer';
                        }
                        if (!mysqli_stmt_execute($stmt2)) {
                            echo mysqli_error($link);

                        }
                        echo "Update:" . $updateData;
                        mysqli_stmt_close($stmt2);

                    }
                    $sql3 = 'UPDATE service_requests SET last_update = unix_timestamp() where requestID = ? and (merchantID = ? or customerID = ?)';
                    if ($stmt3 = mysqli_prepare($link, $sql3)) {
                        mysqli_stmt_bind_param($stmt3, 'sii', $requestID,$_SESSION['id'], $_SESSION['id']);
                        if (mysqli_stmt_execute($stmt3)) {
                            mysqli_stmt_close($stmt3);
                        }

                    }
                }
                elseif (in_array($_POST['action'],['Payment Required','In Progress']) ){
                    $sql2 = 'INSERT INTO service_updates (requestID, userID, userName, userType, updateinfo, updateType) VALUES (?,?,?,?,?,?)';
                    if ($stmt2 = mysqli_prepare($link, $sql2)) {
                        mysqli_stmt_bind_param($stmt2, 'iisssi', $requestID,  $_SESSION['id'], $param_username, $userType, $_POST['action'], $updateType);
                        $userType = 'Merchant';
                        $updateType = 1;
                        if (!mysqli_stmt_execute($stmt2)) {
                            echo mysqli_error($link);

                        }
                        mysqli_stmt_close($stmt2);

                    }

                    $sql2 = 'UPDATE service_requests SET last_update = unix_timestamp(), last_see_merch = unix_timestamp(), status = ? where requestID = ? and merchantID = ?';
                    if ($stmt2 = mysqli_prepare($link, $sql2)) {
                        $new_status = $_POST['action'];
                        mysqli_stmt_bind_param($stmt2, 'ssi', $new_status, $requestID, $_SESSION['id']);
                        if (mysqli_stmt_execute($stmt2)) {

                        }
                        mysqli_stmt_close($stmt2);
                    }
                }

            }
            if($customerName === $param_username || $merchantName === $param_username){

                $timeDiffUpdate = compare_time($last_update);
                $timeDiffMerch = compare_time($last_seen_merch);
                $timeDiffCust = compare_time($last_seen_cust);
                echo "<h3>Service name: $serviceName</h3>";
                echo "<h4>Current Status: $status </h4>";
                echo "<h6>Last update $timeDiffUpdate</h6><br><br>";
                echo "<b>Merchant:</b> $merchantName <p><h6>Last read: $timeDiffMerch</h6>";
                echo "<b>Customer:</b> $customerName <h6>Last read: $timeDiffCust</h6><br><br>";
                if($customerName !== $param_username){
                    $need_payment = true;
                    if(in_array($status, ['In Progress', 'Review'])){
                        echo '<button disabled class="btn btn-primary">In Progresss</button>';
                    }
                    else{
                        echo '<button onclick="" class="btn btn-primary">In Progresss</button>';

                    }
                    if(in_array($status, ['Payment Required', 'Review'])){
                        echo '<button disabled class="btn btn-success">Payment Required</button>';
                        $need_payment = false;
                    }
                    else{
                        echo '<button id="paymentRequiredButton" class="btn btn-success">Payment Required</button>';

                    }

                        echo '<button onclick="markComplete()" class="btn btn-danger">Mark Complete</button>';
                    if($need_payment){
                        echo '<div id="paymentFormDiv" style="display:none; margin: auto; width: 50%; border: grey; outline: 2px solid black;"><form id="paymentForm"><input type="checkbox" id="useOwnAddress" name="useOwnAddress"><label for="useOwnAddress">Use Own Bitcoin Address?</label><input type="text" placeholder="Amount in USD"></form></div>';
                    }

                        if($customerContact !== null){
                            echo '<p id="contactCopyInfo">Click to visit/ copy to clipboard</p>';
                            $unserialized = json_decode($customerContact, true);
                            foreach ($unserialized as $key => $arrayValue) {
                                $arrayValue = htmlspecialchars($arrayValue);
                                if ($key === 'email') {
                                    echo "<a href=\"mailto:$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
                                } elseif (in_array($key, ['discord_server', 'telegram_group', 'website'])) {
                                    echo "<a href=\"$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
                                } elseif (($key === 'other')) {
                                    if (strpos($arrayValue, '<') === false) {
                                        $otherContact = $arrayValue;
                                    }
                                } else {
                                    $safeValue = htmlspecialchars($arrayValue);
                                    echo "<input type=\"text\" style='display: none' value=\"$arrayValue\" id=\"$key$safeValue\">";
                                    echo "<img border=\"0\" alt=\"$arrayValue\" src=\"resources/img/$key.png\" width=\"50\" height=\"50\" onmouseover=\"(copyInfo('$arrayValue'))\" onmouseleave=\"(clearCopyInfo('mouseLeave'))\" onclick=\"setClipboard('$arrayValue')\">";
                                }
                            }
                            echo '<br><br>';
                        }
                    else{
                        echo "<h2>Service completed</h2>";
                    }
                    $sql2 = 'UPDATE service_requests SET last_see_merch = unix_timestamp() WHERE requestID = ?';


                }
                else{
                    $sql2 = 'UPDATE service_requests SET last_seen_cust = unix_timestamp() WHERE requestID = ?';

                }
                if ($stmt2 = mysqli_prepare($link, $sql2)) {
                    mysqli_stmt_bind_param($stmt2, 's', $requestID);
                    if (!mysqli_stmt_execute($stmt2)) {
                        echo 'There was an error updating last seen time';
                    }
                }
                echo '<p style=\"font-size:15px\">';
                    echo '<div style="text-align: center;">
<div style="display: inline-block; text-align: left;">
<table>
<tr><b>
<th style="text-align: center">Form Field</th>
<th style="text-align: center">User Response</th>
</tr></b>
';
                    foreach ($formattedFormData as $data => $dataValue) {
                        if($data !== 'image'){
                            echo "<tr><td><b>$data</td><td>".htmlspecialchars($dataValue). "</td></tr>";
                        }
                        else{
                            echo "<tr><td><b>$data</td><td><img style='max-width: 80%' src='imageuploads/".$dataValue. "'></td></tr>";
                        }

                    }
                    echo '</table></div></div>';
                echo '<h2>Updates</h2>';
                echo '</p>';
            }else{
                echo '<h2>Nothing found1.</h2>';
            }
                $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                echo '<form action="'.$actual_link.'" method="post">
<input type="text" name="updateData">
    <input type="hidden" name="action" value="update">
    <input type="submit">
</form>';
                $sql4 = 'SELECT updateinfo, userName, updateTime, updateType FROM service_updates WHERE requestID = ? ORDER BY updateTime DESC';
                if ($stmt4 = mysqli_prepare($link, $sql4)) {
                    mysqli_stmt_bind_param($stmt4, 'i', $requestID);

                    if (mysqli_stmt_execute($stmt4)) {
                        mysqli_stmt_bind_result($stmt4, $updateInfo, $userName, $updateTime, $updateType);

                        if(mysqli_stmt_fetch($stmt4)){
                            echo '<div style="text-align: center;">
<div style="display: inline-block; text-align: left;">';
                            echo "<table><col width=\"20%\">
  <col width=\"80%\"><tr><b><th style=\"text-align: center\">User</th><th style=\"text-align: center\">Message</th></b></tr>";
                            if($updateType === 0){
                                echo "<tr><td>$userName<p><h6>".compare_time($updateTime)."</h6></td><td>".htmlspecialchars($updateInfo). "</td></tr>";
                            }
                            else{
                                echo "<tr><td>$userName<p><h6>".compare_time($updateTime)."</h6></td><td><b>Marked as: $updateInfo</b></td></tr>";
                            }

                            while (mysqli_stmt_fetch($stmt4)) {
                                if($updateType === 0){
                                    echo "<tr><td>$userName<p><h6>".compare_time($updateTime)."</h6></td><td>".htmlspecialchars($updateInfo). "</td></tr>";
                                }
                                else{
                                    echo "<tr><td>$userName<p><h6>".compare_time($updateTime)."</h6></td><td><b>Marked as: $updateInfo</b></td></tr>";
                                }
                            }
                            echo "</table></div></div>";
                        }
                        else{
                            echo "None";
                        }
                    }
                }
        }
            elseif ($status === 'Review'){

            }
            else{
            echo '<h2>Nothing found.</h2>';
        }



    }

    }
    else{
        mysqli_stmt_close($stmt);
    }

}
mysqli_close($link);

?>

    <script>
        let isCopied = false;
        function copyInfo(data) {
            $('#contactCopyInfo').html(data);
        }
        function clearCopyInfo(method) {
            if(method === 'mouseLeave'){
                if(isCopied === false) {
                    $('#contactCopyInfo').html("Click to visit/ copy to clipboard");
                }
            }
            else{
                isCopied = false;
                $('#contactCopyInfo').html("Click to visit/ copy to clipboard");
            }
        }
        function setClipboard(value) {
            const tempInput = document.createElement("input");
            tempInput.style = "position: absolute; left: -1000px; top: -1000px";
            tempInput.value = value;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            $('#contactCopyInfo').html("Copied to clipboard!");
            isCopied = true;
            setTimeout(clearCopyInfo,1000);
            document.body.removeChild(tempInput);
        }
        function copyClipboard(toCopy) {
            const copyText = document.getElementById(toCopy);
            copyText.style.display = 'block';
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
        }

        function markComplete(){
            let r = confirm("This will delete all data. Are you sure?");
            if(r === true){
                $.post("<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";?>", {
                    requestID: "<?php echo $requestID;?>",
                    action: "completed",

                }).done(function (){location.reload();
                    return false;});
            }
        }
        function markPayment(){
                        $.post("<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";?>", {
                requestID: "<?php echo $requestID;?>",
                action: "Payment Required",

            }).done(function (){location.reload();
                return false;});
        }
        function updateStatus(status){

        }

        $("#paymentRequiredButton").click(function () {
            var paymentFormDiv = $('#paymentFormDiv');
            var returnValPayDiv = false;
            if(paymentFormDiv.is(":hidden")){
                returnValPayDiv = confirm("Would you like to accept bitcoin and use the site as an escrow or use your own supplied address.");
            }
            if(returnValPayDiv || paymentFormDiv.is(":visible")){
                paymentFormDiv.slideToggle(500, function () {
                    $("#paymentRequiredButton").text(function () {
                        return paymentFormDiv.is(":visible") ? "Collapse Form" : "Payment Required";
                    });
                });
            }
            else{
                $.post("<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";?>", {
                    requestID: "<?php echo $requestID;?>",
                    action: "Payment Required",

                }).done(function (){location.reload();
                    return false;});
            }




        });

        $(document).ready(function() {

            $('#useOwnAddress').change(function() {
                if(this.checked) {
                    var returnVal = confirm("Disable using escrow?");
                    $(this).prop("checked", returnVal);
                    if(returnVal){
                        $('#paymentForm').append('<input id="ownBitcoinAddress" type="text" placeholder="Bitcoin Address">')

                    }
                }
                else{
                    $('#ownBitcoinAddress').remove();
                }
            });
        });

    </script>
<?php
include_once 'footer.php';
