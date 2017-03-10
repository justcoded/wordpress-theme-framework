<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 */

use JustCoded\ThemeFramework\Web\View;

View::layout_open(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php View::render( 'page/_content' ); ?>

	<?php endwhile; // End of the loop. ?>

<?php View::layout_close(); ?>
