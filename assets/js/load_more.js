jQuery(document).ready(function () {
    initLoadMore();
})

function initLoadMore() {
    jQuery('.jtf-load-more').on('click', function (e) {
        e.preventDefault();
        var $loadmore = jQuery(this),
            link = $loadmore.attr('href'),
            selector = $loadmore.attr('data-selector'),
            container = $loadmore.attr('data-container'),
            attribute = $loadmore.attr('data-attribute'),
            cont = ( (attribute == 'class' ) ? '.' : '#') + selector;
        jQuery.post(
            link,
            {
                'jtf-selector': selector,
                'jtf-container': container,
                'jtf-attribute': attribute,
                'jtf_load_more': true
            },
            function (data) {
                if (!jQuery.trim(data)) {
                    $loadmore.hide();
                }
                jQuery(cont).append(data);
                var currentpage = link.match(/page\/([0-9]+)/)[1];
                currentpage = ++currentpage;
                $loadmore.attr('href', link.replace(/\/page\/[0-9]+/, '/page/' + currentpage));
            }
        ).fail(function () {
            $loadmore.hide();
        });
        return false;
    });
}