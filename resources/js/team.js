require('jquery-ui/ui/widgets/draggable');

$(function () {

    var fieldHeight = 600, fieldCoef = 0.5, margin = 10, roleAreas = [];

    function selectSettings(id) {
        $.get('/team/get-schema/' + id, function (data) {
            $.each(data.schema.settings, function (k, v) {
                // Only for selects yet.
                $('#schemaForm [name="schema[settings][' + k + ']"] option[value="' + v + '"]').prop('selected', true);
            });

            $.each(data.players, function (index, player) {
                let $player = $('.player[data-id="' + player.id + '"]');

                if (player.settings.position) {
                    $player.show();
                } else {
                    $player.hide();
                }

                replaceRow(index, player.id);

                $.each(player.settings, function (k, v) {
                    if (v === null) {
                        v = 'NULL';
                    } else if (typeof(v) == 'object') {
                        v = JSON.stringify(v);
                    }

                    $('#schemaForm [name="player_settings[' + player.id + '][' + k + ']"]').val(v);
                });
            });

            playersPositions();

            $('#saveSchema').hide();
        })
            .fail(function (data) {

            });
    }

    function replaceRow(index, id) {
        var row = $('#players > tbody > tr[data-id="' + id + '"]');
        if (parseInt(index)) {
            row.insertAfter('#players > tbody > tr:eq(' + (index - 1) + ')');
        } else {
            row.prependTo('#players > tbody');
        }
    }

    function setPosition(player) {
        var pos = JSON.parse($('#schemaForm [name="player_settings[' + player.data('id') + '][position]"]').val());
        player.css({
            left: (pos.x * fieldCoef) + 'px',
            bottom: (pos.y * fieldCoef) + 'px',
            top: 'auto'
        });
    }

    function playersPositions() {
        $('.player:visible').each(function(){
            setPosition($(this));
        });
    }

    // roleAreas setup
    $('.role-area').each(function(){
        var coords = $(this).data('coords');
        roleAreas[roleAreas.length] = coords;
        $(this).css({
            left: ((coords.x[0] * fieldCoef) + margin) + 'px',
            bottom: ((coords.y[0] * fieldCoef) + margin) + 'px',
            width: ((coords.x[1] - coords.x[0]) * fieldCoef) + 'px',
            height: ((coords.y[1] - coords.y[0]) * fieldCoef) + 'px',
            lineHeight: ((coords.y[1] - coords.y[0]) * fieldCoef) + 'px'
        });
    });

    playersPositions();

    // Select Settings
    $('#schemaForm [name="schema[id]"]').change(function () {
        selectSettings($(this).val());
    });

    // show button save
    // only for selects yet
    $('#schemaForm select:not([name="schema[id]"])').change(function(){
        $('#saveSchema').show();
        if (window.matchFunction) {
            matchFunction();
        }
    });

    // open saveAs popup
    $('#createSchemaOpen').click(function(){
        $('#createSchemaBlock').show();
    });

    // Moving players
    $('.player').draggable({
        containment: 'parent',
        stop: function(e, ui) {
            $('#schemaForm [name="player_settings[' + $(this).data('id') + '][position]"]').val(JSON.stringify({
                x : Math.round(ui.position.left / fieldCoef),
                y : fieldHeight - Math.round(ui.position.top / fieldCoef)
            }));
            var temp = {};
            $('.player:visible').each(function(){
                var id = $(this).data('id');
                var pos = JSON.parse($('#schemaForm [name="player_settings[' + id + '][position]"]').val());
                $.each(roleAreas, function(k, v){
                    if (
                        pos.x >= v.x[0] && pos.x <= v.x[1]
                        && pos.y >= v.y[0] && pos.y <= v.y[1]
                    ) {
                        if (!isset(temp[k])) {
                            temp[k] = [];
                        }
                        temp[k][temp[k].length] = {id: id, pos: pos};
                        return false;
                    }
                });
            });
            var keys = [];
            // if you trust the search order of object properties,
            // you can not use an array - "keys"
            $.each(temp, function(k, v){
                keys[keys.length] = parseInt(k);
                if (v.length > 1) {
                    v.sort(function(a, b){
                        if (a.pos.y < b.pos.y) {
                            return 1;
                        } else if (a.pos.y > b.pos.y) {
                            return -1;
                        } else {
                            if (a.pos.x < b.pos.x) {
                                return -1;
                            } else if (a.pos.x > b.pos.x) {
                                return 1;
                            } else {
                                return 0;
                            }
                        }
                    });
                }
            });
            keys.sort(function(a, b){
                return a - b;
            });
            var result = [];
            $.each(keys, function(k, v){
                $.each(temp[v], function(k1, v1){
                    result[result.length] = v1.id;
                });
            });
            $.each(result, function(k, v){
                replaceRow(k, v);
            });
            $('#saveSchema').show();
            if (window.matchFunction) {
                matchFunction();
            }
        }
    });

    // save td width
    $('#players td').each(function(){
        $(this).width($(this).width());
    });
    // fields for replace changes
    var fields = ['position', 'reserve_index'];
    // replace rows
    $('#players > tbody > tr').draggable({
        containment: 'parent',
        helper: 'clone',
        axis: 'y',
        opacity: 0.6,
        stop: function(e, ui) {
            var items = [];
            items[0] = {id: ui.helper.data('id')};
            ui.helper.remove();
            $('#players > tbody > tr').each(function(){
                if (ui.offset.top - $(this).offset().top < $(this).height() / 2) {
                    items[1] = {id: $(this).data('id')};
                    if (items[0].id != items[1].id) {
                        items[0].row = $('#players > tbody > tr[data-id="' + items[0].id + '"]');
                        items[1].row = $(this);
                        for (var i = 0; i <= 1; i++) {
                            items[i].index = $('#players > tbody > tr').index(items[i].row);
                            items[i].player = $('.player[data-id="' + items[i].id + '"]');
                            items[i].val = {};
                            $.each(fields, function(k, v){
                                items[i].val[v] = $('#schemaForm [name="player_settings[' + items[i].id + '][' + v + ']"]').val();
                            });
                        }
                        for (var i = 0; i <= 1; i++) {
                            var j = Math.abs(i - 1);
                            $.each(fields, function(k, v){
                                $('#schemaForm [name="player_settings[' + items[i].id + '][' + v + ']"]').val(items[j].val[v]);
                            });
                            if (items[0].index > items[1].index && i == 1) {
                                var index = items[j].index + 1;
                            } else {
                                var index = items[j].index;
                            }
                            replaceRow(index, items[i].id);
                            if (items[j].val.position != 'NULL') {
                                items[i].player.show();
                                setPosition(items[i].player);
                            } else {
                                items[i].player.hide();
                            }
                        }
                        $('#saveSchema').show();
                        if (window.matchFunction) {
                            matchFunction();
                        }
                    }
                    return false;
                }
            });
        }
    });

    /**
     * Save the schema.
     */

    $('#saveSchema').click(function () {
        $.post('/team/save-schema', $('#schemaForm').serialize(), function (data) {
            $('#saveSchema').hide();
        })
            .fail(function (data) {

            });

        return false;
    });

    /**
     * Create a schema.
     */

    $('#createSchema').click(function () {
        $.post('/team/create-schema', $('#schemaForm').serialize(), function (data) {
            $('#createSchemaBlock').hide();

            $('#schemaForm [name="schema[id]"]')
                .append('<option value="' + data.schema.id + '">' + hsc(data.schema.name) + '</option>')
                .val(data.schema.id);
            $('#createSchemaBlock [name="schema[name]"]').val('');
            $('#removeSchema').show();
            $('#saveSchema').hide();
        })
            .fail(function (data) {

            });

        return false;
    });

    /**
     * Remove the schema.
     */

    $('#removeSchema').click(function(){
        let id = $('#schemaForm [name="schema[id]"]').val();

        $.post('/team/remove-schema', {id: id}, function (data) {
            $('#schemaForm [name="schema[id]"] option[value="' + id + '"]').remove();

            selectSettings($('#schemaForm [name="schema[id]"]').val());

            if ($('#schemaForm [name="schema[id]"] option').length < 2) {
                $('#removeSchema').hide();
            }
        })
            .fail(function (data) {

            });

        return false;
    });

    // roleAreas highlight
    var fieldOffset = $('#field').offset();
    $('#field').mousemove(function(e) {
        $('.role-area').removeClass('hover');
        $('.role-area').each(function(){
            var coords = $(this).data('coords');
            if (
                e.pageX - fieldOffset.left - margin >= coords.x[0] * fieldCoef
                && e.pageX - fieldOffset.left - margin <= coords.x[1] * fieldCoef
                && e.pageY - fieldOffset.top - margin <= (fieldHeight - coords.y[0]) * fieldCoef
                && e.pageY - fieldOffset.top - margin >= (fieldHeight - coords.y[1]) * fieldCoef
            ) {
                $(this).addClass('hover');
                return false;
            }
        });
    });
    $('#field').mouseleave(function() {
        $('.role-area').removeClass('hover');
    });

});
