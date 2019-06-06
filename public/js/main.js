(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/js/main"],{

/***/ "./resources/js/main.js":
/*!******************************!*\
  !*** ./resources/js/main.js ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {/**
 * Add data loaders.
 */
dataLoaders.push({
  url: '/get-users',
  success: function success(data) {
    $('#users').html('');
    $.each(data.users, function (k, v) {
      var $user = $('#stdElements .user').clone().appendTo('#users');
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
  }
});
$(function () {
  /**
   * Create a challenge.
   */
  $(document).on('click', '.create-challenge', function () {
    var $user = $(this).parent();
    $.post('/create-challenge', {
      user_id: $user.data('id')
    }, function () {
      $user.children('.create-challenge').hide(); // let $opponent = $user.clone().prependTo('#fromChallenges');
      //
      // $opponent.children('.create-challenge').hide();
    }).fail(function (data) {});
    return false;
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ 2:
/*!************************************!*\
  !*** multi ./resources/js/main.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /home/vagrant/code/football-stars/resources/js/main.js */"./resources/js/main.js");


/***/ })

},[[2,"/js/manifest","/js/vendor"]]]);