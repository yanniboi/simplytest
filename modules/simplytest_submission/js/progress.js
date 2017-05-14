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
      var url = 'spawn.sh/' + settings.simplytest_submission.container_id + '?token=' + settings.simplytest_submission.container_token,
          preEl = document.getElementById('simplytest_submission_progress'),
          buttonEl = document.getElementById('simplytest_submission_submit'),
          exampleSocket = new WebSocket('wss://' + url);

      // Hide the submit button during build.
      $(buttonEl).hide();

      exampleSocket.onmessage = function (event) {
        var reader = new FileReader();
        reader.onloadend = function () {
          // Update progress when new script starts.
          var expr = "Starting next script";
          if (reader.result.search(expr) != -1) {
            settings.simplytest_submission.percent = settings.simplytest_submission.percent + 10;
            var percent = settings.simplytest_submission.percent + '%';
            $('.progress__bar').css('width', percent);
            $('.progress__percentage').html(percent);
          }

          // Add debug to log output.
          preEl.textContent += reader.result;

          $('#simplytest_submission_autoscroll').each(function(){
            if($(this).prop('checked')) {
              var objDiv = $(preEl);
              if (objDiv.length > 0){
                objDiv[0].scrollTop = objDiv[0].scrollHeight;
              }
            }
          });


        };
        reader.readAsBinaryString(event.data);
      };
      exampleSocket.onclose = function (event) {
        $.getJSON('https://' + url, function (data) {
          // Set progress to 100%.
          $('.progress__bar').css('width', '100%');
          $('.progress__percentage').html('100%');
          if (data.url) {
            // @todo maybe a button instead of instantly redirecting?
            $('#simplytest_submission_redirect').each(function(){
              if($(this).prop('checked')) {
                window.location = data.url;
              }
              else {
                var button = $(buttonEl);
                button.show();
                button.click(function() {
                  window.location = data.url;
                });
              }
            });
          }
        });
      };
    }
  };

})(jQuery, Drupal);
