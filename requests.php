<?php
include_once 'header.php';
?>
    <div id="merchant-list">
        <label>
            <input class="search" placeholder="Search"/>
        </label>
        <table>
            <thead>
            <tr>
                <td class="sort" data-sort="merchantName">Merchant Name</td>
                <td class="sort" data-sort="serviceName">Service Name</td>
                <td class="sort" data-sort="lastUpdated">Last Updated</td>
            </tr>
            </thead>
            <tbody class="list">
<?php
$sql = 'SELECT service_requests.requestID, merchant, serviceName, last_seen_cust, last_update, serviceID FROM service_requests INNER JOIN services ON service_requests.serviceID=services.id WHERE service_requests.customer = ?';
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $param_username);
    $param_username = $_SESSION['username'];
    if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $requestID, $merchantName, $serviceName, $last_seen_cust, $last_update, $serviceID);

        while (mysqli_stmt_fetch($stmt)) {
            $compareTime = compare_time($last_update);
            if($last_update > $last_seen_cust){
                echo '<tr bgcolor="#adff2f">';
            }else{ echo '<tr>';}
            echo "<td class=\"id\" style=\"display:none;\">$requestID</td>";
            echo "<td class='merchantName'>$merchantName</td>";
            echo "<td class='serviceName'><a href='requestData.php?requestID=$serviceID'>$serviceName</a></td>";
            echo "<td class='lastUpdated'><form action='requestData.php' method='get'><button type='submit' class='btn' name='requestID' value='$requestID'>$compareTime</button></form></td></tr>";
        }

    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}
mysqli_stmt_close($stmt);
mysqli_close($link);
include_once 'footer.php';
