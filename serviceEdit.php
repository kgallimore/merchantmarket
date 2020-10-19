<?php
include_once 'header.php';
$serviceID = $_GET['serviceID'];
$count = 0;
if (($_SERVER['REQUEST_METHOD'] === 'POST') && count($_POST) > 1) {
    $sql = 'UPDATE services SET formTypes = ? WHERE id = ? and merchantID = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {
        $newFormTypes = json_encode($_POST);
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, 'ssi', $newFormTypes, $serviceID, $_SESSION['id']);
        // Attempt to execute the prepared statement
        if (!mysqli_stmt_execute($stmt)) {
            echo 'Something went wrong. Please try again later.';
        }
        else{
            echo 'Form submitted. Success!<p>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}
    echo 'Disabled options are a work in progress.
<table>
    <tr>
        <td>
            <button class="btn" onclick="addSmallText()">Add Miscellaneous Text</button>
        </td>

        <td><button class="btn" onclick="addTextSeparator()">Add Information Text</button></td>
        <td><button class="btn" onclick="addOptions()" disabled>Add Options Dropdown</button></td>
        <td><button class="btn" id="addImageUpload" onclick="addImageUpload()">Add Image Upload (Limit of 1)</button></td>
    </tr>
    <tr>
        <td><input type="text" placeholder="Example"></td>
        <td>Example</td>
        <td><select name="Example">
                <option value="Example 1">Example 1</option>
                <option value="Example 2">Example 2</option>
                <option value="Example 3">Example 3</option>
            </select></td>
        <td align="center">Upload Example Image<input type="file" accept="image/png, image/jpeg"></td>
    </tr>


</table>

<form id="editForm" action="serviceEdit.php?serviceID=' . $serviceID . '" method="post">
    <br><button type="submit" class="btn">Submit</button>';

    $sql = 'SELECT formTypes FROM services WHERE id = ? and merchantID = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {

        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, 'ii', $serviceID, $_SESSION['id']);
        // Attempt to execute the prepared statement

        if (!mysqli_stmt_execute($stmt)) {
            echo 'Something went wrong. Please try again later.';
        }
        else {

            mysqli_stmt_bind_result($stmt, $formTypes);


            if (mysqli_stmt_fetch($stmt)) {
                $formTypesArray = json_decode($formTypes);

                foreach ($formTypesArray as $n => $nValue){
                    $fieldType = preg_replace('/[^a-zA-Z]/', '', $n);
                    $fieldNumber = preg_replace('/[^1-9]/', '', $n);
                    $safeNVal = htmlspecialchars($nValue);
                    switch ($fieldType){
                        case "textField":
                            $fieldTypeText = "Input: ";
                            break;
                        case "textSeparator":
                            $fieldTypeText = "Text: ";
                            break;
                        case "imageUpload":
                            $fieldTypeText = "Image: ";
                            break;

                    }
                    echo '<div id=' . $fieldNumber . '>'.$fieldTypeText.'<input name="' . $n . '" id="' . $fieldNumber . '" type="text" value="'.$safeNVal.'" placeholder="Input text field name" minlength="3" size="30"><button type="button" onclick="removeItem(' . $fieldNumber . ')">X</button></div>';
                    $count = $fieldNumber + 1;

                }
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
echo '</form>';
?>

<script>
    const form = $('#editForm');
    let currentNumber = <?php echo (string)$count ?>;

    function addSmallText() {
        form.append('<div id=' + currentNumber.toString() + '>Input: <input name="textField' + currentNumber.toString() + '" id=' + currentNumber.toString() + ' type=text placeholder="Input text field name" minlength="3" size="30"><button type="button" onclick="removeItem(' + currentNumber.toString() + ')">X</button></div>');
        currentNumber++;
    }

    function addOptions() {
        form.append('<div id=' + currentNumber.toString() + '><input name="optionTitle' + currentNumber.toString() + '" type=text placeholder="Options Title" minlength="7" size="30">' +
            '<button type="button" onclick="addOption(' + currentNumber.toString() + ')">Add Option</button><button type="button" onclick="removeItem(' + currentNumber.toString() + ')">X</button></div>');
        currentNumber++;
    }

    function addOption(itemNumber) {
        $('#' + itemNumber).append('<div id=' + currentNumber.toString() + '><input name="' + itemNumber + 'optionOption' + currentNumber.toString() + '" type="text" placeholder="Input option" minlength="7" size="30">' +
            '<button type="button" onclick="removeItem(' + currentNumber.toString() + ')">X</button></div>');
        currentNumber++;
    }

    function addTextSeparator() {
        form.append('<div id=' + currentNumber.toString() + '>Text: <input name="textSeparator' + currentNumber.toString() + '" id="' + currentNumber.toString() + '"  placeholder="Information Text" minlength="3" size="30"><button type="button" onclick="removeItem(' + currentNumber.toString() + ')">X</button></div>');
        currentNumber++;
    }

    function addImageUpload() {
        form.append('<div id=' + currentNumber.toString() + '>Image: <input name="imageUpload' + currentNumber.toString() + '" id="' + currentNumber.toString() + '" type="text" placeholder="Image Upload Name" minlength="3" size="30"><button type="button" onclick="removeItem(' + currentNumber.toString() + ')">X</button></div>');
        $("#addImageUpload").prop('disabled', true);
        currentNumber++;
    }

    function removeItem(itemNumber) {
        $('#' + itemNumber).remove();
    }
</script>
<?php
include_once 'footer.php';

