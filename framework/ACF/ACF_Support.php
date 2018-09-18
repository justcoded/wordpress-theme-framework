<?php

namespace JustCoded\WP\Framework\ACF;


use JustCoded\WP\Framework\Objects\Singleton;

class ACF_Support {
	use Singleton;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'acf/input/admin_head', array( $this, 'register_assets' ) );
		add_action( 'admin_menu', array( $this, 'acf_remove_ui' ) );
	}

	/**
	 * Flexible Collapse Fields and Responsive ACF Fields.
	 */
	public function register_assets() {
		if ( is_admin() ) {
			wp_enqueue_script( '_jtf-acf_collapse', jtf_plugin_url( 'assets/js/acf-flexible-collapse.js' ), [ 'jquery' ] );
			wp_enqueue_style( '_jtf-acf_responsive_fields', jtf_plugin_url( 'assets/css/acf-responsive-columns.css' ) );
		}
	}

	/**
	 * Remove ACF UI from menu.
	 */
	public function acf_remove_ui() {
		remove_menu_page( 'edit.php?post_type=acf-field-group' );
	}

	/**
	 * Check that required plugin is installed and activated
	 *
	 * @return bool
	 */
	public static function check_requirements() {
		return class_exists( 'ACF' );
	}
}