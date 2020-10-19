<?php
include_once 'header.php';
$serviceID = (int)$_GET['serviceID'];
if (is_int($serviceID) && $serviceID < 10000000 && $serviceID > 0) {
    $sql = 'SELECT serviceName, notes, formTypes, status, user, merchantID FROM services WHERE id = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {

        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, 's', $serviceID);

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_bind_result($stmt, $serviceName, $notes, $formTypes, $status, $merchantName, $merchantID);
            mysqli_stmt_fetch($stmt);
            $resultArray = json_decode($formTypes, true);
            if((int)$status === 1){
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mysqli_stmt_close($stmt);
                    if(isset($_SESSION['requestID']) && $serviceID !== $_SESSION['requestID']) {
                        die('Form tampered? Multiple forms at once?');
                    }
                    $input_count = 0;
                    foreach ($resultArray as $n => $nValue){
                        if(strpos($n, 'imageUpload') !== false){
                            $nValue = str_replace(' ', '_', $nValue);
                            if(isset($_FILES[$nValue])){
                                $input_count++;
                                $currentDir = getcwd();
                                $uploadDirectory = 'imageuploads/';
                                if (!file_exists($uploadDirectory) && !mkdir($uploadDirectory, 0744) && !is_dir($uploadDirectory)) {
                                    throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDirectory));
                                }

                                $errors = []; // Store all foreseen and unforseen errors here

                                $fileExtensions = ['jpeg','jpg','png']; // Get all the file extensions

                                $fileName = $_FILES[$nValue]['name'];
                                $fileSize = $_FILES[$nValue]['size'];
                                $fileTmpName  = $_FILES[$nValue]['tmp_name'];
                                $fileType = $_FILES[$nValue]['type'];
                                $explodeFileName = explode('.',$fileName);
                                $fileExtension = strtolower(end($explodeFileName));
                                $newFileName = $serviceID . '_' . $_SESSION['id'] . '_' . date("H.i.s.d.M.Y") . '_' . generateRandomString() . '.' . $fileExtension;
                                $uploadPath = $currentDir . '/' . $uploadDirectory . $newFileName;

                                if (!in_array($fileExtension,$fileExtensions)) {
                                    $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
                                }

                                if ($fileSize > 2000000) {
                                    $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
                                }

                                if (empty($errors)) {
                                    $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

                                    if ($didUpload) {
                                        $_POST['image'] = $newFileName;
                                        echo "<img style='max-width: 50%' src='imageuploads/".$newFileName."'><p>";
                                    } else {
                                        die("An error occurred somewhere. Try again or contact the admin");
                                    }
                                } else {
                                    foreach ($errors as $error) {
                                        die($error);
                                    }
                                }
                            }
                            else{
                                die('Error retrieving file');
                            }

                        }
                        if(strpos($n, 'textField') !== false || strpos($n, 'optionTitle') !== false){
                            ++$input_count;
                        }
                    }
                    foreach ($_POST as $n => $nValue){
                        echo $n . ':' . $nValue . '<br>';
                    }
                    if(count($_POST) !== $input_count){
                        die('Form tampered');
                    }
                    $formData = json_encode($_POST);
                    $sql2 = 'INSERT INTO service_requests (customer, merchant, serviceID, formData, customerID, merchantID) VALUES (?,?,?,?,?,?)';

                    if ($stmt2 = mysqli_prepare($link, $sql2)) {
                        // Bind variables to the prepared statement as parameters
                        mysqli_stmt_bind_param($stmt2, 'ssisii',$username,$merchantName, $serviceID, $formData, $userID, $merchantID);
                        $userID = $_SESSION['id'];
                        // Attempt to execute the prepared statement
                        if (!mysqli_stmt_execute($stmt2)) {
                            echo 'Something went wrong. Please try again later.';
                        }
                        else{
                            echo '<h2>Form submitted successfully!</h2>';
                        }
                        mysqli_stmt_close($stmt2);
                    } else {
                        echo mysqli_error($link);
                        echo 'Oops! Something went wrong. Please try again later.';
                    }
                }
                else if(count($resultArray)>0){
                    echo '<b>Notes: </b>' . $notes;
                    $_SESSION['requestID'] = $serviceID;
                    echo '<form action="serviceRequest.php?serviceID=' . $serviceID . '" method="post" enctype="multipart/form-data">';
                    echo '<h2>' .$serviceName. '</h2>';
                    echo $notes . '<br><br><br>';
                    foreach ($resultArray as $n => $nValue){
                        $nValue = htmlspecialchars($nValue);
                        if(strpos($n, 'textField') !== false){
                            echo "<input minlength='3' type=\"text\" placeholder=\"$nValue\" name=\"$nValue\"><br>";
                        }
                        if(strpos($n, 'optionTitle') !== false){
                            $itemNumber = substr($n,11);
                            $optionNumber = (int)$itemNumber + 1;
                            $concatenate = $itemNumber.'optionOption'.(string)$optionNumber;
                            echo "<select name=\"$nValue\">";
                            while(isset($_POST[$concatenate])){
                                echo "<option value=\"$_POST[$concatenate]\">$_POST[$concatenate]</option>";
                                ++$optionNumber;
                                $concatenate = $itemNumber.'optionOption'. $optionNumber;
                            }
                            echo '</select><br>';
                        }
                        if(strpos($n, 'textSeparator') !== false){
                            echo $nValue.'<br>';
                        }
                        if(strpos($n, 'imageUpload') !== false){
                            $cleanFile = str_replace(' ', '_', $nValue);
                            $cleanFile = preg_replace('([/\\\])', '', $cleanFile);

                            echo $nValue . ': <input type="file" name="'.$cleanFile.'" id="'.$cleanFile.'" style="display: inline "><br>';
                        }
                    }
                    echo '<br><button type="submit" class="btn">Submit</button></form><br>';
                    mysqli_stmt_close($stmt);
                }
                else{
                    echo '<b>Notes: </b>' . $notes;
                    echo '<br>Merchant has not created a form for this service yet';
                }
            }
            else{

                echo 'No such service exists';
            }

        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }
    }
    } else {
        echo 'ServiceID out of range or non int';
    }
echo '</body></html>';

