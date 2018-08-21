<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Traits;

use JustCoded\WP\Framework\Page_Builder\v25\Fields\Field_Select_Posts;
use JustCoded\WP\Framework\Page_Builder\v25\Fields\Field_Select_Terms;

trait Widget_Fields_Loader {

	/**
	 * Fields constructor.
	 * (has to be called in)
	 */
	protected function fields_loader() {
		add_filter( 'siteorigin_widgets_field_class_prefixes', array( $this, 'fields_class_prefixes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

		// register ajax for custom fields.
		add_action( 'wp_ajax_pagebuilder_field_select_terms_search', array( Field_Select_Terms::class, 'ajax_search_terms' ) );
		add_action( 'wp_ajax_pagebuilder_field_select_posts_search', array( Field_Select_Posts::class, 'ajax_search_posts' ) );
	}

	/**
	 * Register and enqueue select2 script in the WordPress admin post edit screen.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return boolean
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
			return false;
		}

		wp_enqueue_style(
			'select2',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'assets/css/select2.min.css',
			false,
			'4.0.6-rc.1'
		);

		wp_enqueue_script(
			'select2',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'assets/js/select2.min.js',
			array( 'jquery' ),
			'4.0.6-rc.1'
		);
		return true;
	}

	/**
	 * Registering custom class prefix.
	 *
	 * @param array $class_prefixes - Class prefixes.
	 *
	 * @return array
	 */
	public function fields_class_prefixes( $class_prefixes ) {
		$class_prefixes[] = '\JustCoded\WP\Framework\Page_Builder\v25\Fields\Field_';

		return $class_prefixes;
	}

}
