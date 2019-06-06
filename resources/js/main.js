/**
 * Add data loaders.
 */

dataLoaders.push({
    url: '/get-users',
    success: function (data) {
        $('#users').html('');

        $.each(data.users, function (k, v) {
            let $user = $('#stdElements .user').clone().appendTo('#users');

            $user.attr('data-id', v.id);
            $user.children('.name').html(hsc(v.name));

            $user.children('.play').hide();
            $user.children('.remove-challenge').hide();

            if (v.is_match) {
                $user.addClass('match');
            }

            if (v.online) {
                $user.addClass('online');
            }

            if (!v.can_get_challenge) {
                $user.children('.create-challenge').hide();
            }
        });
    },
});

$(function () {
    /**
     * Create a challenge.
     */

    $(document).on('click', '.create-challenge', function () {
        let $user = $(this).parent();

        $.post('/create-challenge', {user_id: $user.data('id')}, function () {
            $user.children('.create-challenge').hide();

            // let $opponent = $user.clone().prependTo('#fromChallenges');
            //
            // $opponent.children('.create-challenge').hide();
        })
            .fail(function (data) {

            });

        return false;
    });
});
