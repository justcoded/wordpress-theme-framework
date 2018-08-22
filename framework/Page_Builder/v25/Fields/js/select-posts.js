/* global jQuery, soWidgets */

(function ($) {

  $(document).on('sowsetupformfield', '.siteorigin-widget-field-type-select-posts', function (e) {
    var $$ = $(this);

    if ($$.data('initialized')) {
      return;
    }

    $$.find('select').select2({
      minimumInputLength: 2,
      ajax: {
        url: soWidgets.ajaxurl,
        data: function (params) {
          return {
            action: 'pagebuilder_field_select_posts_search',
            term: params.term,
            types: $$.find('select').data('post_types'),
            selected: $$.find('select').val()
          };
        },
        dataType: 'json'
      }
    });

    $$.data('initialized', true);
  });

})(jQuery);
