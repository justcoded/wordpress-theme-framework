jQuery(document).ready(function () {
  initLoadMore();
})

function initLoadMore() {
  var loadmore_active = false;
  jQuery('.jtf-load-more').on('click', function (e) {
    e.preventDefault();
    if (loadmore_active) return false;
    loadmore_active = true;
    var $loadmore = jQuery(this),
      link = $loadmore.attr('href'),
      pagecount = $loadmore.attr('data-pagecount'),
      selector = $loadmore.attr('data-selector'),
      container = $loadmore.attr('data-container') || selector;
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
        loadmore_active = false;
      }
    ).fail(function () {
      $loadmore.hide();
    });
    return false;
  });
}