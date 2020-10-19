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
                <td class="sort" data-sort="serviceName">Service Name</td>
                <td class="sort" data-sort="customerName">Customer Name</td>
                <td class="sort" data-sort="lastUpdated">Last Updated</td>
            </tr>
            </thead>
            <tbody class="list">
<?php
$sql = 'SELECT service_requests.requestID, customer, serviceName, last_see_merch, last_update, serviceID, service_requests.status FROM service_requests INNER JOIN services ON service_requests.serviceID=services.id WHERE service_requests.merchant = ?';
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $param_username);
    $param_username = $_SESSION['username'];
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $requestID, $customerName, $serviceName, $last_seen_merch, $last_update, $serviceID, $status);

        while (mysqli_stmt_fetch($stmt)) {
            $compareTime = compare_time($last_update);
            if($last_update > $last_seen_merch){
                echo '<tr bgcolor="#adff2f">';
            }else{ echo '<tr>';}
            echo "<td class=\"id\" style=\"display:none;\">$requestID</td>";
            echo "<td class='serviceName'><a href='serviceEdit.php?serviceID=$serviceID'>$serviceName</a></td>";
            echo "<td class='customerName'>$customerName</td>";
            if($status !== 'Complete'){
                echo "<td class='lastUpdated'><form action='requestData.php' method='get'><button type='submit' class='btn' name='requestID' value='$requestID'>$compareTime</button></form></td></tr>";
            }
            else{
                echo "<td class='lastUpdated'><form action='requestData.php' method='get'><button type='submit' class='btn' name='requestID' value='$requestID'>Completed</button></form></td></tr>";
            }

        }
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }


}
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
        </table>
    </div>
<?php
include_once 'footer.php';
?>

