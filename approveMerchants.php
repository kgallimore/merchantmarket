<?php
require_once 'header.php';
?>
    <div id="merchant-list">
        <label>
            <input class="search" placeholder="Search"/>
        </label>
        <table>
            <thead>
            <tr>
                <td class="sort" data-sort="username">Username</td>
                <td class="sort" data-sort="application">Application</td>
                <td class="sort" data-sort="list">List</td>
                <td class="contactInfo">Contact Info</td>
            </tr>
            </thead>
            <tbody class="list">
<?php
$sql = "SELECT username, request_merchant_list, request_merchant_application, contactinfo FROM users WHERE role = 'rmerchant'";

if ($stmt = mysqli_prepare($link, $sql)) {
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $name, $request_merchant_list, $request_merchant_application, $contactInfo);
        while (mysqli_stmt_fetch($stmt)) {
                echo "<td class=\"username\">$name</td><td class='application'>$request_merchant_application</td><td class='list'>$request_merchant_list</td><td class='contactInfo'>";
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
                echo '</td></tr>';

        }
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($link);
?>
<script>

    const options = {
        valueNames: ['merchant', 'services', 'costs', 'lastLogin']
    };

    // Init list
    const contactList = new List('merchant-list', options);

</script>
<?php
include_once 'footer.php';
