/**
 * @file
 * Simplytest.me submission progress tracking.
 *
 * @todo Use websocket library with fallbacks (?)
 * @todo Add handling for expired containers (404 response from spawn.sh)
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.batch = {
    attach: function (context, settings) {
      var url = 'spawn.sh/' + settings.simplytest_submission.container_id + '?token=' + settings.simplytest_submission.container_token;
      var preEl = document.getElementById('simplytest_submission_progress');
      var exampleSocket = new WebSocket('wss://' + url);
      exampleSocket.onmessage = function (event) {
        var reader = new FileReader();
        reader.onloadend = function () {
          // @todo some better way to display the log? with autoscrolling?
          preEl.textContent += reader.result;
        };
        reader.readAsBinaryString(event.data);
      };
      exampleSocket.onclose = function (event) {
        $.getJSON('https://' + url, function (data) {
          if (data.url) {
            // @todo maybe a button instead of instantly redirecting?
            window.location = data.url;
          }
        });
      };
    }
  };

})(jQuery, Drupal);