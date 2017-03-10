<?php
/**
 * The template for 404 Error not found.
 */

use JustCoded\ThemeFramework\Web\View;
use justtheme\App\Admin\ThemeSettings;

View::layout_open();

$title = ThemeSettings::get('404_title');
$content = ThemeSettings::get('404_content');
?>

	<section class="error-404 not-found">
		<header class="page-header">
			<h1 class="page-title"><?php echo apply_filters('the_title', $title); ?></h1>
		</header><!-- .page-header -->

		<div class="page-content">
			<?php echo apply_filters('the_content', $content); ?>

			<?php get_search_form(); ?>
		</div><!-- .page-content -->
	</section><!-- .error-404 -->

<?php View::layout_close(); ?>
