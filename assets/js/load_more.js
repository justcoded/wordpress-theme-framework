jQuery(document).ready(function () {
  initLoadMore();
});

function initLoadMore() {
  var loading = false;
  jQuery('.jtf-load-more').on('click', function (e) {
    e.preventDefault();
    if (loading) return false;
    loading = true;
    var $loadmore = jQuery(this),
      link = $loadmore.attr('href'),
      pagecount = $loadmore.data('pagecount'),
      selector = $loadmore.data('selector'),
      container = $loadmore.data('container') || selector;
    jQuery.post(
      link,
      {
        'jtf_selector': selector,
        'jtf_load_more': true
      },
      function (data) {
        if (!jQuery.trim(data)) {
          $loadmore.hide();
        }
        jQuery(container).append(data);
        var currentpage = link.match(/page\/([0-9]+)/)[1];
        $loadmore.attr('href', link.replace(/\/page\/[0-9]+/, '/page/' + ++currentpage));
        if (currentpage > pagecount) {
          $loadmore.hide();
        }
        loading = false;
      }
    ).fail(function () {
      $loadmore.hide();
    });
    return false;
  });
}