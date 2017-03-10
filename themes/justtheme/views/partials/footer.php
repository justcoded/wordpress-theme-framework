<?php \JustCoded\ThemeFramework\Web\View::footer_begin(); ?>
<footer id="colophon" class="site-footer" role="contentinfo">
	<div class="site-info">
		<?php
		$copy = justtheme\App\Admin\ThemeSettings::get('copyright_text');
		echo $copy;
		?>

		<span class="sep"> | </span>

		<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'justtheme' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'justtheme' ), 'WordPress' ); ?></a>
	</div><!-- .site-info -->
</footer><!-- #colophon -->