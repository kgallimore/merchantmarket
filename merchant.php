<?php
require_once 'config/config.php';

$merchantName = $_GET['merchantName'];

$sql = 'SELECT news, id, contactinfo FROM users WHERE username = ?';

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $merchantName);

    if (mysqli_stmt_execute($stmt)) {

        //$resultant = mysqli_stmt_get_result($stmt);
        mysqli_stmt_bind_result($stmt, $news, $id, $contactinfo);
        if (mysqli_stmt_fetch($stmt)) {

        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}
mysqli_stmt_close($stmt);

$room = $id;
$roomtype = 'user';
include 'chat.php';
include 'header.php';
?>
<style>
    #discordWidget {
        position: fixed;
        bottom: 5px;
        left: 5px;
        margin: 5px;
    }

    #service-list {
        position: relative;
        margin: auto;
        width: 80%;
        alignment: center;
    }
</style>
<?php
if ($merchantName[-1] === 's') {
    $safeMerchantName = htmlspecialchars($merchantName . "'");
} else {
    $safeMerchantName = htmlspecialchars($merchantName . "'s");
}

echo "<h1>$safeMerchantName Services</h1>";
if (isset($contactinfo)) {
    echo '<p id="contactCopyInfo">Click to visit/ copy to clipboard</p>';
    $afterContacts = '';
    $unserialized = json_decode($contactinfo);
    foreach ($unserialized as $key => $arrayValue) {
        $arrayValue = htmlspecialchars($arrayValue);
        if ($key === 'email') {
            echo "<a href=\"mailto:$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
        } elseif (in_array($key, ['discord_server', 'telegram_group', 'website'])) {
            echo "<a href=\"$arrayValue\"><img border=\"0\" alt=\"$arrayValue\" src=\"resources\img\/$key.png\" width=\"50\" height=\"50\"></a>";
        } elseif ($key === 'discord_server_widget') {
            $afterContacts .= "<div id=\"discordWidget\"><iframe src=\"https://ptb.discordapp.com/widget?id=$arrayValue&theme=dark\" width=\"350\" height=\"500\" allowtransparency=\"true\" frameborder=\"0\"></iframe></div>";
        } elseif ($key === 'skype_chat') {
            $afterContacts .= "<div class=\"skype-chat\" data-contact-id=\"$arrayValue\"></div>";
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
    if ($otherContact !== '') {
        echo $afterContacts;
    }
    if ($otherContact !== null) {
        echo "<br>Other contact option: $otherContact";
    }
}

if (isset($news)) {
    echo '<h3><b>News:</b>' .htmlspecialchars($news). '</h3>';
} else {
    echo '<h3><b>No News</b></h3>';
} ?>
<div class="page-header">
</div>
<div id="service-list">
    <input class="search" placeholder="Search"/>
    <table>
        <thead>
        <tr>
            <td class="sort" data-sort="serviceType">Service Type</td>
            <td class="sort" data-sort="serviceName">Service Name</td>
            <td class="sort" data-sort="countries">Countries</td>
            <td class="sort" data-sort="costs">Costs</td>
            <td class="sort" data-sort="notes">Notes</td>
        </tr>
        </thead>
        <tbody class="list">
        <?php
        $sql = 'SELECT serviceType, serviceName, cost, notes, id, countries FROM services WHERE user = ?';

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $merchantName);

            if (mysqli_stmt_execute($stmt)) {

                //$resultant = mysqli_stmt_get_result($stmt);
                mysqli_stmt_bind_result($stmt, $serviceType, $serviceName, $cost, $notes, $serviceID, $countries);
                while (mysqli_stmt_fetch($stmt)) {
                    $countriesSub = substr($countries, 0, -1);
                    Echo "<td class='serviceType'>$serviceType</td>";
                    Echo "<td class=\"serviceName\"><a href=serviceRequest.php?serviceID=$serviceID>$serviceName</a></td>";
                    echo "<td class='countries'>$countriesSub</td>";
                    echo "<td class='costs'>$cost</td>";
                    echo "<td class='notes'>$notes</td>";
                    echo '</tr>';
                }


            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
        }
        mysqli_stmt_close($stmt);
        ?>
        </tbody>
    </table>
</div>
</body>
<script>
    var isCopied = false;
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
    const options = {
        valueNames: ['serviceType', 'serviceName', 'countries', 'costs', 'notes']
    };

    // Init list
    const serviceList = new List('service-list', options);

</script>
<?php
include_once 'footer.php';

