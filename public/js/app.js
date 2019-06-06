(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/js/app"],{

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");

window.socketIo = io.connect('//' + window.location.hostname + ':8080');
socketIo.on('tokenRequest', function () {
  var token = $('meta[name="jwt"]').attr('content');

  if (token) {
    $('meta[name="jwt"]').attr('content', '');
    socketIo.emit('token', token);
  } else {
    $.get('/jwt', function (data) {
      socketIo.emit('token', data.token);
    });
  }
});
window.dataLoaders = [{
  url: '/get-common-data',
  success: function success(data) {
    $('#fromChallenges').html('');
    $('#toChallenges').html('');
    $.each(data.fromChallenges, function (k, v) {
      var $user = $('#stdElements .user').clone().appendTo('#fromChallenges');
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
      var $user = $('#stdElements .user').clone().appendTo('#toChallenges');
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
  }
}];
window.socketHandler = {
  userConnected: [function (data) {
    $('.user[data-id="' + data.userId + '"]').addClass('online');
  }],
  userDisconnected: [function (data) {
    $('.user[data-id="' + data.userId + '"]').removeClass('online');
  }],
  fromChallengeCreated: [function (data) {
    var $user = $('#stdElements .user').clone().appendTo('#fromChallenges');
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
  }],
  toChallengeCreated: [function (data) {
    var $user = $('#stdElements .user').clone().appendTo('#toChallenges');
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
  }],
  fromChallengeRemoved: [function (data) {//
  }],
  toChallengeRemoved: [function (data) {//
  }]
};
var completeDataLoadsCount = 0,
    socketMessages = [];

function isAllDataLoaded() {
  return completeDataLoadsCount == dataLoaders.length;
}

function handleSocketMessage(data) {
  data = JSON.parse(data);

  if (socketHandler[data.event]) {
    $.each(socketHandler[data.event], function (k, v) {
      v(data);
    });
  }
}

function completeDataLoad(jqXHR, textStatus) {
  if (textStatus == 'success') {
    completeDataLoadsCount++;

    if (isAllDataLoaded()) {
      $.each(socketMessages, function (k, v) {
        handleSocketMessage(v);
      });
    }
  }
}

socketIo.on('successfulConnection', function () {
  $(function () {
    socketIo.on('message', function (data) {
      if (isAllDataLoaded()) {
        handleSocketMessage(data);
      } else {
        socketMessages.push(data);
      }
    });
    $.each(dataLoaders, function (k, v) {
      $.ajax({
        url: v.url,
        success: v.success,
        complete: completeDataLoad
      });
    });
  });
});
$(function () {
  /**
   * Remove challenge.
   */
  $(document).on('click', '.remove-challenge', function () {
    var $user = $(this).parent();
    $.post('/remove-challenge', {
      user_id: $user.data('id')
    }, function () {
      //$('#users .user[data-id="' + $user.data('id') + '"]').children('.create-challenge').show();
      $user.remove();
    }).fail(function (data) {});
    return false;
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

window.isset = function (variable) {
  return typeof variable != 'undefined';
};

window.hsc = function (string) {
  return $('<div/>').text(string).html();
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ 1:
/*!***********************************!*\
  !*** multi ./resources/js/app.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /home/vagrant/code/football-stars/resources/js/app.js */"./resources/js/app.js");


/***/ })

},[[1,"/js/manifest","/js/vendor"]]]);