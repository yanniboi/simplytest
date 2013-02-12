(function ($) {
  Drupal.behaviors.simplytest_advanced = {
    attach: function (context, settings) {
      $(document).ready(function () {

        var advanced = $('#simplytest-submission-form #edit-advanced');
        if (advanced.length > 0) {

          // "Add an addtional module" function.
          $('#edit-additionals .form-item', advanced).each(function() {
            // First hide all textfields, which have no default value set.
            if (!$('input', this).val()) {
              $(this).hide();
            }
            // Add a remove link to each textfield, to hide and clear it.
            $(this).append('<a class="remove-field" title="' + Drupal.t('Remove') + '">×</a>');
            $('.remove-field', this).click(function() {
              $(this).parent().slideUp();
              $('input', $(this).parent()).val('');
            });
          })
          $('#edit-add-additional', advanced).show().click(function(e) {
            e.preventDefault();
            // Every time the add another button gets clicked, make another
            // one of the textfields visible.
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
            // First hide all textfields, which have no default value set.
            if (!$('input', this).val()) {
              $(this).hide();
            }
            // Add a remove link to each textfield, to hide and clear it.
            $(this).append('<a class="remove-field" title="' + Drupal.t('Remove') + '">×</a>');
            $('.remove-field', this).click(function() {
              $(this).parent().slideUp();
              $('input', $(this).parent()).val('');
            });
          })
          $('#edit-add-patch', advanced).show().click(function(e) {
            e.preventDefault();
            // Every time the add another button gets clicked, make another
            // one of the textfields visible.
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
