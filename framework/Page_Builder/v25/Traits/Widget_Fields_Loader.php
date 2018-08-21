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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pb_admin_style' ) );

		// register ajax for custom fields.
		add_action( 'wp_ajax_custom_term_search', array( Field_Select_Terms::class, 'ajax_search_terms' ) );
		add_action( 'wp_ajax_custom_post_search', array( Field_Select_Posts::class, 'ajax_search_posts' ) );
	}

	/**
	 * Register and enqueue a custom stylesheet in the WordPress admin.
	 */
	public function enqueue_pb_admin_style() {
		wp_enqueue_style(
			'custom_wp_admin_css',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/css/fields.css',
			false,
			'12.1'
		);
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
