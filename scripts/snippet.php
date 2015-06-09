<?php

// Make sure snippet is executed at the and of a request.
register_shutdown_function(function() {
  global $simplytest_snippet;
  // Make sure the accepted content is text/html.
  if (isset($simplytest_snippet) && isset($_SERVER['HTTP_ACCEPT']) && strstr($_SERVER['HTTP_ACCEPT'], 'text/html') !== FALSE) {
    // Make sure the returned content is also text/html.
    $headers = headers_list();
    foreach ($headers as $header) {
      $header = strtolower($header);
      if (strstr($header, 'content-type:') !== FALSE) {
        if (strstr($header, 'content-type: text/html') !== FALSE) {
          // Everything is fine, print the snippet.
          _simplytest_snippet_infobar($simplytest_snippet);
          return;
        }
        else {
          return;
        }
      }
    }
  }
});

/**
 * Prints out the infobar snippet for showing time left and other info.
 */
function _simplytest_snippet_infobar($variables) {
  extract($variables);
  $save_project = htmlspecialchars($project, ENT_QUOTES, 'UTF-8');
  $save_version = htmlspecialchars($version, ENT_QUOTES, 'UTF-8');
  // Calculate time left in seconds and pass it into the js.
  $seconds = ($timeout * 60 + $created_timestamp) - time();
  ?>
  <html>
    <head>
      <meta name="viewport" content="width=device-width">
      <style>
        #simplytest-snippet-container * {
          color: #fff;
          font-size: 16px !important;
          font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
          line-height: 1.2;
        }

        #simplytest-snippet-container {
          position: fixed;
          bottom: 0;
          z-index: 2147483647;
          padding: 0 5px;
          border: 1px solid transparent;
          border-radius: 5px;
          background-color: rgb(155,155,155);
          background-color: rgba(0,0,0,0.5);
          color: #fff !important;
          text-shadow: black 0.1em 0.1em 0.2em, black 0.1em 0.1em 0.2em;
          font-weight: bold;
          cursor: default;
        }

        #simplytest-snippet-container.st-warn {
          border: 1px solid #BD362F;
          background-color: #D86761;
        }

        #simplytest-snippet-infobar.st-hide {
          display: none;
        }

        #simplytest-snippet-open {
          display: none;
          font-size: 19px !important;
          cursor: pointer;
        }

        #simplytest-snippet-open.st-show {
          display: inline;
        }

        #simplytest-snippet-close:hover {
          color: red;
        }

        #simplytest-snippet-backlink {
          margin-right: 10px;
          margin-left: 10px;
          text-decoration: none;
        }

        #simplytest-snippet-close {
          cursor: pointer;
        }

        @media only screen and (max-width: 18.125em) {
          #simplytest-snippet-backlink {
            display: none;
          }
        }
      </style>
    </head>
    <body>
      <div id="simplytest-snippet-container">
        <span id="simplytest-snippet-infobar">
          <span id="simplytest-snippet-countdown-time"></span>
          <a href="<? print 'http://simplytest.me/project/' . urlencode($project) . '/' . urlencode($version); ?>" id="simplytest-snippet-backlink" title="Back to simplytest.me"><?php print $save_project; ?> <?php print $save_version; ?></a>
          <span id="simplytest-snippet-close" title="Hide bar">&#x2718;</span>
        </span>
        <span id="simplytest-snippet-open" title="Show bar">&#8801;</span>
      </div>
      <script>
        (function () {
          "use strict";

          // Don't display the bar inside iframes.
          if (window.self !== window.top) {
            document.getElementById('simplytest-snippet-container').style.display = 'none';
            return;
          }

          // getElementById alias.
          var get = function(id) {
            return document.getElementById(id);
          };

          // The countdown timer.
          var counter = function() {
            var end, delta, counterElement;

            function formatNumber(n) {
              return ((n < 10) ? '0' : '') + n;
            };
            function updateCountDown() {
              delta = end - (new Date().getTime());

              // Warn when 1 min left.
              if (delta <= 60000) {
                barContainer.className = 'st-warn';
              }
              if (delta >= 0){
                var d = new Date(delta);
                var days = d.getUTCDate() - 1;
                if (days > 0) {
                  days = formatNumber(days) + ':';
                }
                else {
                  days = '';
                }
                var hh = formatNumber(d.getUTCHours());
                var mm = formatNumber(d.getUTCMinutes());
                var ss = formatNumber(d.getUTCSeconds());
                counterElement.innerHTML = days + hh + ':' + mm + ':' + ss;
              } else {
                counterElement.innerHTML = 'Time over!';
                window.location = 'http://simplytest.me/';
              }
            };
            return {
              init: function (seconds, counter_id) {
                counterElement = get(counter_id);
                end = new Date().getTime() + (1000 * seconds);
                updateCountDown();
                setInterval(updateCountDown, 990);
              }
            };
          }();

          var barContainer = get('simplytest-snippet-container');
          var barElement = get('simplytest-snippet-infobar');
          var barClose = get('simplytest-snippet-close');
          var barOpen = get('simplytest-snippet-open');
          var toggle = false;

          // Initialize countdown.
          counter.init(<?php echo $seconds ?>, 'simplytest-snippet-countdown-time');

          // Bar hide/show toggling.
          var toggleSimplytestInfobar = function (e){
            if (e) { e.preventDefault(); }
            barElement.className = (toggle) ? '' : 'st-hide';
            barOpen.className = (toggle) ? '' : 'st-show';
            toggle = !toggle;
          };
          barClose.onclick = toggleSimplytestInfobar;
          barOpen.onclick = toggleSimplytestInfobar;

          // Preset form fields (admin username / password, mysql credentials).
          if (get('edit-site-name') !== null) {
            get('edit-site-name').value = '<?php echo "$save_project $save_version" ?>';
          }
          if (get('edit-name') !== null && get('edit-pass') !== null) {
            get('edit-name').value = '<?php echo $admin_user ?>';
            get('edit-pass').value = '<?php echo $admin_user ?>';
          }
          if (get('edit-account-name') !== null && get('edit-account-pass-pass1') !== null) {
            get('edit-account-name').value = '<?php echo $admin_user ?>';
            get('edit-account-pass-pass1').value = '<?php echo $admin_user ?>';
            get('edit-account-pass-pass2').value = '<?php echo $admin_user ?>';
          }
          if (get('edit-mysql-database') !== null) {
            get('edit-mysql-database').value = '<?php echo $mysql ?>';
          }
          if (get('edit-mysql-username') !== null) {
            get('edit-mysql-username').value = '<?php echo $mysql ?>';
          }
          if (get('edit-mysql-password') !== null) {
            get('edit-mysql-password').value = '<?php echo $mysql ?>';
          }
          if (get('edit-db-path') !== null) {
            get('edit-db-path').value = '<?php echo $mysql ?>';
          }
          if (get('edit-db-user') !== null) {
            get('edit-db-user').value = '<?php echo $mysql ?>';
          }
          if (get('edit-db-pass') !== null) {
            get('edit-db-pass').value = '<?php echo $mysql ?>';
          }
          if (get('edit-site-mail') !== null) {
            get('edit-site-mail').value = '<?php echo $id . $mail_suffix ?>';
          }
          if (get('edit-account-mail') !== null) {
            get('edit-account-mail').value = '<?php echo $id . $mail_suffix ?>';
          }
        }());
      </script>
    </body>
  </html>
<?php } ?>
