const env = require('dotenv').config({path: __dirname + '/.env'}).parsed,
    io = require('socket.io')(env.SOCKET_IO_PORT),
    jwt = require('jsonwebtoken'),
    redis = require('redis'),
    publisher = redis.createClient(),
    users = {};

/**
 * Clear online after start of server.
 */

publisher.publish('system', JSON.stringify({event: 'serverRestarted'}));

/**
 * Connect to a new socket.
 */

io.on('connection', function (socket) {
    socket.user = {};

    requestToken(socket);

    socket.on('disconnect', function () {
        clearTimers(socket);

        if (socket.user.id) {
            disconnectUser(socket);
        }
    });

    socket.on('token', function (token) {
        jwt.verify(token, env.JWT_SECRET, function (err, decoded) {
            if (decoded) {
                clearTimers(socket);

                if (!socket.user.id) {
                    socket.user.id = decoded.sub;
                    connectUser(socket);
                }

                socket.user.timeout = setTimeout(function () {
                    socket.user.timeout = setTimeout(function () {
                        disconnectUser(socket);
                    }, env.WAITING_TOKEN_TIME);

                    requestToken(socket);
                }, decoded.exp * 1000 - Date.now());
            }
        });
    });
});

/**
 * Request a token.
 *
 * @param socket
 */
function requestToken(socket) {
    socket.emit('tokenRequest');
    socket.user.interval = setInterval(function () {
        socket.emit('tokenRequest');
    }, env.REQUEST_TOKEN_INTERVAL);
}

/**
 * Clear socket's timeout and interval.
 *
 * @param socket
 */
function clearTimers(socket) {
    if (socket.user.timeout) {
        clearTimeout(socket.user.timeout);
    }
    if (socket.user.interval) {
        clearInterval(socket.user.interval);
    }
}

/**
 * Add redis subscriber to socket and send notification to system channel.
 *
 * @param socket
 */
function connectUser(socket) {
    let userId = socket.user.id,
        subscriber = redis.createClient();

    subscriber.subscribe(['user:' + userId, 'all']);
    subscriber.on('message', function (channel, data) {
        socket.emit('message', data);
    });
    socket.user.subscriber = subscriber;

    if (!users[userId]) {
        users[userId] = {connectionsCount: 0}
    }
    users[userId].connectionsCount++;

    if (users[userId].connectionsCount == 1) {
        publisher.publish('system', JSON.stringify({
            event: 'userConnected',
            userId: userId,
        }));
    }

    socket.emit('successfulConnection');
}

/**
 * Quit redis subscriber and send notification to system channel.
 *
 * @param socket
 */
function disconnectUser(socket) {
    let userId = socket.user.id;

    socket.user.id = null;
    socket.user.subscriber.quit();

    users[userId].connectionsCount--;

    if (users[userId].connectionsCount == 0) {
        publisher.publish('system', JSON.stringify({
            event: 'userDisconnected',
            userId: userId,
        }));
    }
}
