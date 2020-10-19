const express = require('express');
const http = require('http');
const path = require('path');
const socketIO = require('socket.io');
const app = express();
const server = http.Server(app);
const io = socketIO(server);
app.set('port', 5000);
app.use('/static', express.static(__dirname + '/static'));// Routing
app.get('/', function (request, response) {
    response.sendFile(path.join(__dirname, 'livechat/chat.php'));
});// Starts the server.
server.listen(5000, function () {
    console.log('Starting server on port 5000');
});
// Add the WebSocket handlers
io.on('connection', function (socket) {
});
var usersTyping = [];
var usersTypingMessage;

function sendCurrentlyTyping(user) {
    if (usersTyping.length > 1) {
        usersTypingMessage = usersTyping.join(', ') + " are currently typing...";
    } else if (usersTyping.length === 1) {
        usersTypingMessage = user + ' is currently typing';
    } else {
        usersTypingMessage = '';
    }
    io.sockets.emit('currentlyTypingMessage', usersTypingMessage);
}

io.on('connection', function (socket) {

    socket.on('joinChat', function (joinMessage) {
        let userJoined = joinMessage + " has joined the chat";
        io.sockets.emit('joinedChat', userJoined);
    });

    socket.on('message', function (user, message) {
        //messageToSend = "<b>" + user + ":</b> " + message;
        io.sockets.emit('message', user, message);
    });

    socket.on('currentlyTyping', function (user) {
        if (!usersTyping.includes(user)) {
            usersTyping.push(user);
            sendCurrentlyTyping(user);
        }
    });

    socket.on('doneTyping', function (user) {
        usersTyping.splice(usersTyping.indexOf(user), 1);
        sendCurrentlyTyping(user);
    });


});
