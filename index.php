<?php

require_once 'header.php';
$room = 1;
$roomtype = 'public';
//include "chat.php";

?>

<div id="merchant-list">
    <label>
        <input class="search" placeholder="Search"/>
    </label>
    <table>
        <thead>
        <tr>
            <td class="sort" data-sort="merchant">Merchant</td>
            <td class="sort" data-sort="services">Services (Click to expand)</td>
            <td class="sort" data-sort="completedServices">Completed</td>
            <td class="sort" data-sort="lastLogin">Last Seen</td>
        </tr>
        </thead>
        <tbody class="list">
        <?php
        $link2 = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        // Check connection
        if ($link2 === false) {
            die('ERROR: Could not connect. ' . mysqli_connect_error());
        }
        $sql = "SELECT username, services, last_seen, created_at, services_completed FROM users WHERE role = 'merchant' OR role = 'admin' ";

        if ($stmt = mysqli_prepare($link, $sql)) {
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_bind_result($stmt, $name, $services, $lastSeen, $createdTime, $completedServices);
                while (mysqli_stmt_fetch($stmt)) {
                    $servicesArray = explode(',', $services);
                    if ($services !== null && $services !== '') {
                        Echo "<td class=\"merchant\"><a class='btn btn-default' href=merchant.php?merchantName=$name>$name</a></td><td class='services'>";
                        foreach ($servicesArray as $service) {
                            Echo "<button onclick='expand(this)' class=\"collapsible\">$service</button><div class=\"content\">";
                            $sql2 = 'SELECT serviceName, cost, notes, id, countries FROM services WHERE user = ? AND serviceType = ?';
                            if ($stmt2 = mysqli_prepare($link2, $sql2)) {
                                mysqli_stmt_bind_param($stmt2, 'ss', $name, $service);
                                if (mysqli_stmt_execute($stmt2)) {
                                    mysqli_stmt_bind_result($stmt2, $serviceName, $cost, $notes, $serviceID, $countries);
                                    $countriesSub = substr($countries, 0, -1);
                                    while (mysqli_stmt_fetch($stmt2)) {
                                        echo "<p><a href=serviceRequest.php?serviceID=$serviceID>$serviceName: $cost:$countriesSub</a></p>";
                                    }
                                } else {
                                    echo 'Oops! Something went wrong. Please try again later.';
                                }
                                mysqli_stmt_close($stmt2);
                            }
                            echo '</div>';

                        }
                        $timeDiff = time() - $lastSeen;
                        if ($timeDiff < 30) {
                            $compareTime = 'Now';
                        } elseif ($timeDiff < 60) {
                            $compareTime = '1 minute ago';
                        } elseif ($timeDiff < 3600) {
                            $extraTime = round($timeDiff / 60);
                            $compareTime = "$extraTime minutes ago";
                        } elseif ($timeDiff < 5400) {
                            $extraTime = round($timeDiff / 3600);
                            $compareTime = '1 hour ago';
                        } elseif ($timeDiff < 172800) {
                            $extraTime = round($timeDiff / 3600);
                            $compareTime = "$extraTime hours ago";
                        } elseif ($lastSeen === 0) {
                            $compareTime = 'Never';
                        } else {
                            $extraTime = round($timeDiff / 86400);
                            $compareTime = "$extraTime days ago";
                        }
                        echo "</div></td><td class='completedServices' style='width:15%;'>$completedServices</td>";
                        $simpleCreatedTime = substr($createdTime, 0, 10);
                        echo "<td class='lastLogin'><p>Last seen: $compareTime</p><p>Signed up: $simpleCreatedTime</p></td>";
                        echo '</tr>';
                    }
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($link);
        ?>
        </tbody>
    </table>
</div>
<script>

    const options = {
        valueNames: ['merchant', 'services', 'costs', 'lastLogin']
    };

    // Init list
    const contactList = new List('merchant-list', options);

</script>
<?php
include 'footer.php';
