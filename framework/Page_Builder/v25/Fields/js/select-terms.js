/* global jQuery, soWidgets */

(function ($) {

  $(document).on('sowsetupformfield', '.siteorigin-widget-field-type-select-terms', function (e) {
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
            action: 'pagebuilder_field_select_terms_search',
            term: params.term,
            taxonomies: $$.find('select').data('taxonomies'),
            selected: $$.find('select').val()
          };
        },
        dataType: 'json'
      }
    });

    $$.data('initialized', true);
  });

})(jQuery);
