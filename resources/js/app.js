require('./bootstrap');

/**
 * Create array of data loaders.
 */

window.dataLoaders = [
    {
        url: '/get-common-data',
        success: function (data) {
            $('#fromChallenges').html('');
            $('#toChallenges').html('');

            $.each(data.fromChallenges, function (k, v) {
                let $user = $('#stdElements .user').clone().appendTo('#fromChallenges');

                $user.attr('data-id', v.to_user.id);
                $user.children('.name').html(hsc(v.to_user.name));

                $user.children('.create-challenge').hide();
                $user.children('.play').hide();

                if (v.to_user.is_match) {
                    $user.addClass('match');
                }

                if (v.to_user.online) {
                    $user.addClass('online');
                }
            });

            $.each(data.toChallenges, function (k, v) {
                let $user = $('#stdElements .user').clone().appendTo('#toChallenges');

                $user.attr('data-id', v.from_user.id);
                $user.children('.name').html(hsc(v.from_user.name));

                $user.children('.create-challenge').hide();

                if (v.from_user.is_match) {
                    $user.addClass('match');
                    $user.children('.play').hide();
                }

                if (v.from_user.online) {
                    $user.addClass('online');
                } else {
                    $user.children('.play').hide();
                }
            });
        },
    },
];

/**
 * Create array of socket handlers.
 */

window.socketHandlers = [
    {
        events: ['userConnected'],
        handler: function (data) {
            $('.user[data-id="' + data.userId + '"]').addClass('online');
            $('#toChallenges .user[data-id="' + data.userId + '"]').children('.play').show();
        },
    },
    {
        events: ['userDisconnected'],
        handler: function (data) {
            $('.user[data-id="' + data.userId + '"]').removeClass('online');
            $('#toChallenges .user[data-id="' + data.userId + '"]').children('.play').hide();
        },
    },
    {
        events: ['fromChallengeCreated'],
        handler: function (data) {
            if (!$('#fromChallenges .user[data-id="' + data.user.id + '"]').length) {
                let $user = $('#stdElements .user').clone().appendTo('#fromChallenges');

                $user.attr('data-id', data.user.id);
                $user.children('.name').html(hsc(data.user.name));

                $user.children('.create-challenge').hide();
                $user.children('.play').hide();

                if (data.user.is_match) {
                    $user.addClass('match');
                }

                if (data.user.online) {
                    $user.addClass('online');
                }
            }
        },
    },
    {
        events: ['toChallengeCreated'],
        handler: function (data) {
            if (!$('#toChallenges .user[data-id="' + data.user.id + '"]').length) {
                let $user = $('#stdElements .user').clone().appendTo('#toChallenges');

                $user.attr('data-id', data.user.id);
                $user.children('.name').html(hsc(data.user.name));

                $user.children('.create-challenge').hide();

                if (data.user.is_match) {
                    $user.addClass('match');
                    $user.children('.play').hide();
                }

                if (data.user.online) {
                    $user.addClass('online');
                } else {
                    $user.children('.play').hide();
                }
            }
        },
    },
    {
        events: ['challengeRemoved'],
        handler: function (data) {
            $('#fromChallenges .user[data-id="' + data.userId + '"]').remove();
            $('#toChallenges .user[data-id="' + data.userId + '"]').remove();
        },
    },
    {
        events: ['matchStarted'],
        handler: function (data) {
            $.each(data.userIds, function (k, v) {
                $('.user[data-id="' + v + '"]').addClass('match');
                $('#toChallenges .user[data-id="' + data.userId + '"]').children('.play').hide();
            });
        },
    },
    {
        events: ['myMatchStarted'],
        handler: function (data) {
            $('#toChallenges .user .play').hide();

            let $opponent = $('#toChallenges .user[data-id="' + data.userId + '"]');

            $('#myMatchStarted').show();
        },
    },
];

let completeDataLoadsCount = 0,
    socketMessages = [];

function isAllDataLoaded() {
    return completeDataLoadsCount == dataLoaders.length;
}

function handleSocketMessage(data) {
    data = JSON.parse(data);

    $.each(socketHandlers, function (k, v) {
        if ($.inArray(data.event, v.events) != -1) {
            v.handler(data);
        }
    });
}

function completeDataLoad(jqXHR, textStatus) {
    if (textStatus == 'success') {
        completeDataLoadsCount++;

        if (isAllDataLoaded()) {
            $.each(socketMessages, function (k, v) {
                handleSocketMessage(v);
            });

            socketMessages = [];
        }
    }
}

/**
 * Connect to server.
 */

const socketIoPort = window.location.protocol == 'https:' ? process.env.MIX_SOCKET_IO_HTTPS_PORT : process.env.MIX_SOCKET_IO_HTTP_PORT,
    socketIo = io.connect('//' + window.location.hostname + ':' + socketIoPort);

socketIo.on('tokenRequest', function () {
    let token = $('meta[name="jwt"]').attr('content');

    if (token) {
        $('meta[name="jwt"]').attr('content', '');
        socketIo.emit('token', token);
    } else {
        $.get(
            '/jwt',
            function (data) {
                socketIo.emit('token', data.token);
            }
        );
    }
});

socketIo.on('successfulConnection', function () {
    $(function () {
        completeDataLoadsCount = 0;
        socketMessages = [];

        $.each(dataLoaders, function (k, v) {
            $.ajax({
                url: v.url,
                success: v.success,
                complete: completeDataLoad,
            });
        });
    });
});

$(function () {
    /**
     * Close popup window.
     */

    $('.popup').click(function (event) {
        event.stopPropagation();

        $(this).hide();
    });

    $('.popup-content').click(function (event) {
        event.stopPropagation();
    });

    /**
     * On socket message.
     */

    socketIo.on('message', function (data) {
        if (isAllDataLoaded()) {
            handleSocketMessage(data);
        } else {
            socketMessages.push(data);
        }
    });

    /**
     * Remove the challenge.
     */

    $(document).on('click', '.remove-challenge', function () {
        let $user = $(this).parent();

        $.post('/remove-challenge', {user_id: $user.data('id')}, function () {
            $user.remove();
        })
            .fail(function (data) {

            });

        return false;
    });

    /**
     * Start the match.
     */

    $(document).on('click', '.play', function () {
        let $user = $(this).parent();

        $.post('/play', {user_id: $user.data('id')}, function () {
            $user.remove();
        })
            .fail(function (data) {

            });

        return false;
    });
});
