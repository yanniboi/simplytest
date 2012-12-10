(function ($) {
  Drupal.behaviors.simplytest_sponsors = {
    attach: function (context, settings) {
      setInterval(function() { 
        $('.sponsor-ad-slide > div:first')
          .slideUp(1000)
          .next()
          .slideDown(1000)
          .end()
          .appendTo('.sponsor-ad-slide');
      }, 5000);
    }
  };
}(jQuery));
