jQuery(document).ready(function () {
    initLoadMore();
})

function initLoadMore() {
    jQuery('#jtf-load-more').on('click', function (e) {
        e.preventDefault();
        var link = jQuery('#jtf-load-more').attr('href'),
            selector = jQuery('#jtf-load-more').attr('data-selector'),
            container = jQuery('#jtf-load-more').attr('data-container'),
            attribute = jQuery('#jtf-load-more').attr('data-attribute'),
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
                if (!data) jQuery('#jtf-load-more').hide();
                jQuery(cont).append(data);
                var currentpage = link.match(/page\/([0-9]+)/)[1];
                currentpage = ++currentpage;
                jQuery('#jtf-load-more').attr('href', link.replace(/\/page\/[0-9]+/, '/page/' + currentpage));
            }
        );
        return false;
    });
}