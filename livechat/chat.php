<?php
require_once "../config/config.php";
?>
<html lang="en">

<script src="/socket.io/socket.io.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
<style>
    #chatModal {
        display: none;
        position: relative;
        border-radius: 5px;
        right: 10px;
        background-color: #6f7882;
        left: 10px;
        padding: 5px;
    }
</style>
<div id='load-chat' style="position: absolute; left: 0; top: 25px">
    <button id="loadChatButton" class='btn' style="position: relative; bottom: 20px; left: 20px" onclick="start_chat()">
        Show Chat
    </button>
    <div id="chatModal">
        <h2 align="center">Live chat</h2>
        <p id="chat"></p>
        <form id="chatForm">
            <label>
                <input id="chatInput" name='message' placeholder="Send Message" type="text" maxlength="255"
                       pattern="[\\\-\/a-zA-Z0-9._ +=()*&^%$,?<>!@#~`|]+">
                <input name="room" value="<?php echo $room ?>" style="display: none">
                <input name="roomtype" value="<?php echo $roomtype ?>" style="display: none">
                <input type="submit" style="display: none"/>
            </label>
        </form>
        <div id="currentlyTyping"></div>
    </div>
</div>
<script>

    var socket = io();
    var join = '<?php echo $_SESSION['username']?>';
    var room = '<?php echo $room?>';
    var roomtype = '<?php echo $roomtype?>';
    var isTyping = false;
    var loadChatBtn = $('#loadChatButton');
    var message;
    var chat;


    $(document).ready(function () {
        var $form = $('#chatForm');
        $form.submit(function () {
            message = document.getElementById("chatInput").value;
            socket.emit('message', join, message);

            return false;
        });
    });

    var intervalId;

    function start_chat() {
        if (document.getElementById("chatModal").style.display !== "block") {
            $('#chatModal').show();
            chat = $("#chat");
            socket.on('message', function (user, message) {

                chat.append("<br><b>" + user + ":</b> ");
                chat.append(document.createTextNode(message));
            });
            socket.on('joinedChat', function (user) {
                chat.append("<br><b>" + user + "</b>");
            });
            socket.on('currentlyTypingMessage', function (data) {
                $('#currentlyTyping').text(data);
            });
            socket.emit('joinChat', join);
            loadChatBtn.html('Hide Chat');
        } else {
            $('#chatModal').hide();
            clearInterval(chatInterval);
            loadChatBtn.html('Show Chat');
        }
    }


    $('#chatInput').keydown(function (event) {
        if (event.keyCode === 13 || event.which === 13) {
            $('#chatForm').submit();
            $('#chatInput').val("");
            socket.emit('doneTyping', join);
            isTyping = false;
            event.preventDefault();
        } else if ((event.keyCode === 8 || event.which === 8)) {
            if (isTyping === true) {
                socket.emit('doneTyping', join);
                isTyping = false;
            }
        } else if (isTyping === false) {
            socket.emit('currentlyTyping', join);
            isTyping = true;
        }
    });
</script>
</html>

