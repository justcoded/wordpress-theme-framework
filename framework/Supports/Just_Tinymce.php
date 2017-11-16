<?php
namespace JustCoded\WP\Framework\Supports;

/**
 * Class Just_Tinymce
 *
 * Patch config file path
 *
 * @package JustCoded\WP\Framework\Supports
 */
class Just_Tinymce {
	/**
	 * Just_Tinymce constructor.
	 *
	 * Register plugin hooks
	 */
	public function __construct() {
		add_filter( 'jtmce_config_file_path', array( $this, 'jtmce_config_file_path' ) );
	}

	/**
	 * Rewrite configuration path for the plugin.
	 *
	 * @param string $path Path suggested by plugin.
	 *
	 * @return string
	 */
	public function jtmce_config_file_path( $path ) {
		return get_stylesheet_directory() . '/config/just-tinymce.json';
	}
}
