(function($) {

  $(document).ready(function(){

    $('.preview-item').hide();
    $('.preview-item:first').show();
    $('.preview-nav .prev').hide();

    // buttons
    $(document).on('click', '.preview-nav .next', function(){
      var $current = $('.preview-item:visible');
      var $next    = $current.next();
      $current.hide();
      $next.show();

      $('.preview-nav .prev').show();
      if ( ! $next.next().length ) {
        $(this).hide();
      }
    });
    $(document).on('click', '.preview-nav .prev', function(){
      var $current = $('.preview-item:visible');
      var $prev    = $current.prev();
      $current.hide();
      $prev.show();

      $('.preview-nav .next').show();
      if ( ! $prev.prev().length ) {
        $(this).hide();
      }
    })

  })

})(jQuery);