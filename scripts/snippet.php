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
      <style type="text/css" media="all">
        #simplytest-snippet-container * {
            font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 16px !important;
            line-height: 1.2;
            color: #fff;
        }
        #simplytest-snippet-container {
            position:fixed;
            bottom: 0;
            cursor:default;
            color: #fff !important;
            z-index: 9999999999;
            font-weight:bold;
            padding: 0 5px;
            background-color: rgb(155,155,155);
            background-color: rgba(0,0,0,0.5);
            color: white; text-shadow: black 0.1em 0.1em 0.2em, black 0.1em 0.1em 0.2em;
            border: 1px solid transparent;
            border-radius:5px;
        }
        #simplytest-snippet-container.st-warn{
            background-color: #D86761;
            border: 1px solid #BD362F;
        }
        #simplytest-snippet-infobar.st-hide{
           display: none;
        }
        #simplytest-snippet-open {
            display: none;
            font-size: 19px !important;
            cursor:pointer;
        }
        #simplytest-snippet-open.st-show{
            display: inline;
        }
        #simplytest-snippet-close:hover{
            color:red;
        }
        #simplytest-snippet-backlink {
            margin-left: 10px;
            margin-right: 10px;
            text-decoration:none;
        }
        #simplytest-snippet-qr-code {
            position: relative;
            top: 2px;
            margin-left: 10px;
        }
        #simplytest-snippet-close,
        #simplytest-snippet-qr-code {
            cursor:pointer;
        }
        @media only screen and (max-width: 18.125em) {
            #simplytest-snippet-backlink {
                display:none;
            }
            #simplytest-snippet-qr-code {
              display: none;
            }
        }
      </style>
    </head>
    <body>
      <div id="simplytest-snippet-container">
        <span id="simplytest-snippet-infobar">
          <span id="simplytest-snippet-countdown-time"></span>
          <img id="simplytest-snippet-qr-code" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAM1BMVEUAAAD29vb+/v76+vr4+Pj09PT5+fnz8/Pw8PD4+Pj19fX29vbz8/P39/fy8vLu7u75+flIu1cCAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdBQ4NAQBbiP8AAAAAaUlEQVQY02WNCQ6AIAwER0XAouD/X2vLZaJTGja7TQs/1luBC/tXNXxtNwQctZzrAsta3gWXgfddDJZlypT0pUIO7NIsEYSYCTvvXIGgkcnTUEMnEJsIrSNjR65V3h2x99yxFYWNz5XJAxK4A86fTNrwAAAAAElFTkSuQmCC" title="Generate QR code" />
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
            var end, delta, counter_element;

            function formatNumber(n) {
              return ((n < 10) ? "0" : "") + n;
            };
            function updateCountDown() {
              delta = end - (new Date().getTime());

              if (delta <= 60000) { // warn when 1 min left
                bar_container.className = 'st-warn';
              }
              if (delta >=0){
                var d = new Date(delta);
                var hh = formatNumber(d.getUTCHours());
                var mm = formatNumber(d.getUTCMinutes());
                var ss = formatNumber(d.getUTCSeconds());
                counter_element.innerHTML = hh + ':' + mm + ':' + ss;
              } else {
                counter_element.innerHTML = 'Time over!';
                window.location = 'http://simplytest.me/';
              }
            };
            return {
              init: function (seconds, counter_id) {
                counter_element = get(counter_id);
                end = new Date().getTime() + (1000 * seconds);
                updateCountDown();
                setInterval(updateCountDown, 990);
              }
            };
          }();

          var bar_container = get('simplytest-snippet-container');
          var bar_element = get('simplytest-snippet-infobar');
          var bar_qr_code = get('simplytest-snippet-qr-code');
          var bar_close = get('simplytest-snippet-close');
          var bar_open = get('simplytest-snippet-open');
          var toggle = false;

          // Initialize countdown.
          counter.init(<?php echo $seconds ?>, 'simplytest-snippet-countdown-time');

          // Bar hide/show toggling.
          var toggle_simplytest_infobar = function (e){
            if (e) { e.preventDefault(); }
            bar_element.className = (toggle) ? '' : 'st-hide';
            bar_open.className = (toggle) ? '' : 'st-show';
            toggle = !toggle;
          };
          bar_close.onclick = toggle_simplytest_infobar;
          bar_open.onclick = toggle_simplytest_infobar;

          // QR code functionality.
          var display_qr_code = function (e) {
            if (e) { e.preventDefault(); }
            var currentURL = window.location.hostname + window.location.pathname;
            var width = 200;
            var height = 200;
            var wx = (screen.width - width) >> 1;
            var wy = (screen.height - height) >> 1;
            var url = "http://chart.googleapis.com/chart?cht=qr&chl=http://" + currentURL + "&chs=200x200";
            window.open(url, '', "top=" + wy + ",left=" + wx + ",width=" + width + ",height=" + height);
          };
          bar_qr_code.onclick = display_qr_code;

          // Preset form fields (admin username / password, mysql credentials).
          if (get('edit-name') !== null && get('edit-pass') !== null) {
            get('edit-name').value = "<?php echo $admin_user ?>";
            get('edit-pass').value = "<?php echo $admin_user ?>";
          }
          if (get('edit-account-name') !== null && get('edit-account-pass-pass1') !== null) {
            get('edit-account-name').value = "<?php echo $admin_user ?>";
            get('edit-account-pass-pass1').value = "<?php echo $admin_user ?>";
            get('edit-account-pass-pass2').value = "<?php echo $admin_user ?>";
          }
          if (get('edit-mysql-database') !== null) {
            get('edit-mysql-database').value = "<?php echo $mysql ?>";
          }
          if (get('edit-mysql-username') !== null) {
            get('edit-mysql-username').value = "<?php echo $mysql ?>";
          }
          if (get('edit-mysql-password') !== null) {
            get('edit-mysql-password').value = "<?php echo $mysql ?>";
          }
          if (get('edit-db-path') !== null) {
            get('edit-db-path').value = "<?php echo $mysql ?>";
          }
          if (get('edit-db-user') !== null) {
            get('edit-db-user').value = "<?php echo $mysql ?>";
          }
          if (get('edit-db-pass') !== null) {
            get('edit-db-pass').value = "<?php echo $mysql ?>";
          }
          if (get('edit-site-mail') !== null) {
            get('edit-site-mail').value = "<?php echo $id . $mail_suffix ?>";
          }
          if (get('edit-account-mail') !== null) {
            get('edit-account-mail').value = "<?php echo $id . $mail_suffix ?>";
          }
        }());
      </script>
    </body>
  </html>
<?php } ?>
