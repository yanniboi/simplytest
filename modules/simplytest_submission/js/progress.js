/**
 * @file
 * Simplytest.me submission progress tracking.
 *
 * @todo Use websocket library with fallbacks.
 */

(function (Drupal) {

  'use strict';

  Drupal.behaviors.batch = {
    attach: function (context, settings) {
      var preEl = document.getElementById('simplytest_submission_progress');
      var exampleSocket = new WebSocket('wss://spawn.sh/' + settings.simplytest_submission.container_id + '?token=' + settings.simplytest_submission.container_token);
      exampleSocket.onmessage = function (event) {
        var reader = new FileReader();
        reader.onloadend = function () {
          preEl.textContent += reader.result;
        };
        reader.readAsBinaryString(event.data);
      };
    }
  };

})(Drupal);
