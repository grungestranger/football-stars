const env = require('dotenv').config({path: __dirname + '/.env'}).parsed,
    app = require('express')(),
    http = require('http').createServer(app).listen(env.SOCKET_IO_HTTP_PORT),
    io = require('socket.io')(http),
    jwt = require('jsonwebtoken'),
    redis = require('redis'),
    publisher = redis.createClient(),
    users = {};

if (env.HTTPS_CERT_PATH && env.HTTPS_KEY_PATH && env.SOCKET_IO_HTTPS_PORT) {
    const fs = require('fs'),
        https = require('https').createServer({
            cert: fs.readFileSync(env.HTTPS_CERT_PATH),
            key: fs.readFileSync(env.HTTPS_KEY_PATH),
        }, app)
            .listen(env.SOCKET_IO_HTTPS_PORT);

    io.attach(https);
}

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

    subscriber.subscribe([env.USER_CHANNEL_PREFIX + userId, env.ALL_CHANNEL]);
    subscriber.on('message', function (channel, data) {
        socket.emit('message', data);
    });
    socket.user.subscriber = subscriber;

    if (!users[userId]) {
        users[userId] = {connectionsCount: 0}
    }
    users[userId].connectionsCount++;

    if (users[userId].connectionsCount == 1) {
        publisher.publish(env.SYSTEM_CHANNEL, JSON.stringify({
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
        publisher.publish(env.SYSTEM_CHANNEL, JSON.stringify({
            event: 'userDisconnected',
            userId: userId,
        }));
    }
}
