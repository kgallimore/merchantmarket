<?php
require_once 'config/config.php';
//if ($_SERVER["REQUEST_METHOD"] == "POST") {
//    $room = $_POST['room'];
//}
?>
<html lang="en">
<script src="node_modules/socket.io/lib/socket.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
<style>
    #chatModal {
        display: none;
        position: absolute;
        border-radius: 5px;
        background-color: #6f7882;
    }
</style>
<div id='load-chat' style="position: absolute; left: 0; top: 25px">
    <button id="loadChatButton" class='btn' style="position: relative; bottom: 20px; left: 20px" onclick="start_chat()">
        Show Chat
    </button>
    <div id="chatModal">
        <h2>Live chat</h2>
        <p id="chat"></p>
        <form id="chatForm" action="postChat.php" method="post">
            <label>
                <input id="chatInput" name='message' placeholder="Send Message" type="text" maxlength="255"
                       pattern="[\\\-\/a-zA-Z0-9._ +=()*&^%$,?<>!@#~`|]+">
                <input name="room" value="<?php echo $room ?>" style="display: none">
                <input name="roomtype" value="<?php echo $roomtype ?>" style="display: none">
                <input type="submit" style="display: none"/>
            </label>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        var $form = $('#chatForm');
        $form.submit(function () {
            $.post($(this).attr('action'), $(this).serialize(), function (response) {
                $('#chatInput').val("");
            }, 'json');
            return false;
        });
    });
    var intervalId;

    function start_chat() {
        if (document.getElementById("chatModal").style.display !== "block") {
            chatInterval = setInterval(send_data, 2000);
            $('#chatModal').show();
            $('#loadChatButton').html('Hide Chat');
        } else {
            $('#chatModal').hide();
            clearInterval(chatInterval);
            $('#loadChatButton').html('Show Chat');
        }
    }

    $('#chatInput').keypress(function (event) {
        if (event.keyCode === 13 || event.which === 13) {
            $('#chatForm').submit();
            $('#chatInput').val("");
            event.preventDefault();
        }
    });

    function send_data() {
        $.post("fetchChat.php", {room: "<?php echo $room?>", roomtype: "<?php echo $roomtype?>"})
            .done(function (data) {
                console.log("Data Loaded: " + data);
                $('#chat').html(data);
            });

    }
</script>
</html>

