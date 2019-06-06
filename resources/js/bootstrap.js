$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.isset = function (variable) {
    return typeof(variable) != 'undefined';
};

window.hsc = function (string) {
    return $('<div/>').text(string).html();
};
