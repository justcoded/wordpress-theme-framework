<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23/04/18
 * Time: 10:44
 */

namespace JustCoded\WP\Framework\Page_Builder\v25\Traits;


trait Fields_Loader {

	/**
	 * Fields constructor.
	 * (has to be cold in)
	 */
	function fields_loader() {
		add_filter( 'siteorigin_widgets_field_class_prefixes', array( $this, 'pb_fields_class_prefixes' ) );

		add_filter( 'siteorigin_widgets_field_class_paths', array( $this, 'pb_fields_class_paths' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pb_admin_style' ) );

		add_action( 'wp_ajax_custom_term_search', array( 'Just_Field_Taxonomy', 'action_search_terms' ) );

		add_action( 'wp_ajax_custom_post_search', array( 'Just_Field_Post', 'action_search_posts' ) );

	}

	/**
	 * Register and enqueue a custom stylesheet in the WordPress admin.
	 */
	function enqueue_pb_admin_style() {
		wp_enqueue_style( 'custom_wp_admin_css', plugin_dir_url( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/css/fields.css', false, '12.1' );
	}

	/**
	 * Path for Page Builder fields.
	 *
	 * @param array $class_paths - Class paths.
	 *
	 * @return array
	 */
	function pb_fields_class_paths( $class_paths ) {
		$class_paths[] = plugin_dir_path( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/';

		return $class_paths;
	}

	/**
	 * Registering custom class prefix.
	 *
	 * @param array $class_prefixes - Class prefixes.
	 *
	 * @return array
	 */
	function pb_fields_class_prefixes( $class_prefixes ) {
		$class_prefixes[] = 'Just_Field_';

		return $class_prefixes;
	}

}
