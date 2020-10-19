var socket = io();
socket.on('message', function (data) {
    console.log(data);
});
var movement = {
    up: false,
    down: false,
    left: false,
    right: false
};
document.addEventListener('keydown', function (event) {
    switch (event.keyCode) {
        case 65: // A
            movement.left = true;
            break;
        case 87: // W
            movement.up = true;
            break;
        case 68: // D
            movement.right = true;
            break;
        case 83: // S
            movement.down = true;
            break;
    }
});
document.addEventListener('keyup', function (event) {
    switch (event.keyCode) {
        case 65: // A
            movement.left = false;
            break;
        case 87: // W
            movement.up = false;
            break;
        case 68: // D
            movement.right = false;
            break;
        case 83: // S
            movement.down = false;
            break;
    }
});
socket.emit('new player');
setInterval(function () {
    socket.emit('movement', movement);
}, 1000 / 60);
var canvas = document.getElementById('canvas');
canvas.width = 1900;
canvas.height = 900;
var context = canvas.getContext('2d');
var context2 = canvas.getContext('2d');
socket.on('state', function (players, npcs) {
    context.clearRect(0, 0, 1900, 900);
    context.fillStyle = 'green';
    for (var id in players) {
        var player = players[id];
        context.beginPath();
        context.arc(player.x, player.y, 10, 0, 2 * Math.PI);
        context.fill();
    }
    context2.fillStyle = 'red';
    for (var x in npcs) {
        var npc = npcs[x];
        context.beginPath();
        context.drawImage(npc.x, npc.y, 10, 0, 2 * Math.PI);
        context.fill();
    }
});

function addNPC() {
    socket.emit('addNPC');
}