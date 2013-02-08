(function ($) {
  Drupal.behaviors.simplytest_advanced = {
    attach: function (context, settings) {
      $(document).ready(function () {

        var advanced = $('#simplytest-submission-form #edit-advanced');
        if (advanced.length > 0) {

          // "Add an addtional module" function.
          $('#edit-additionals .form-item', advanced).each(function() {
            if (!$('input', this).val()) {
              $(this).hide();
            }
          })
          $('#edit-add-additional', advanced).show().click(function(e) {
            e.preventDefault();
            $('#edit-additionals .form-item', advanced).each(function() {
              if (!$(this).is(':visible')) {
                $(this).slideDown();
                $('input', this).focus();
                return false;
              }
            });
          })

          // "Add a patch" function.
          $('#edit-patches .form-item', advanced).each(function() {
            if (!$('input', this).val()) {
              $(this).hide();
            }
          })
          $('#edit-add-patch', advanced).show().click(function(e) {
            e.preventDefault();
            $('#edit-patches .form-item', advanced).each(function() {
              if (!$(this).is(':visible')) {
                $(this).slideDown();
                $('input', this).focus();
                return false;
              }
            });
          })

          // Attach autocomplete functionality to additional projects.
          $(".additionals-autocomplete").autocomplete({
            source: function( request, response ) {
              $.ajax({
                url: Drupal.settings.basePath + 'simplytest/additionals/autocomplete',
                dataType: "json",
                data: {
                  string: request.term
                },
                success: function( data ) {
                  response( $.map( data, function( item ) {
                    return {
                      label: item.label,
                      value: item.shortname
                    }
                  }));
                }
              });
            },
            minLength: 1,
            open: function() {
              $( this ).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function() {
              $( this ).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
          });

		    }
      });
    }
  };
}(jQuery));