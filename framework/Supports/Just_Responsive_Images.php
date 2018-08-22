<?php
namespace JustCoded\WP\Framework\Supports;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class Just_Responsive_Images
 *
 * Load rwd sizes from /config/image-sizes.php file inside the theme.
 *
 * @package JustCoded\WP\Framework\Supports
 */
class Just_Responsive_Images {
	use Singleton;

	/**
	 * Just_Responsive_Images constructor.
	 *
	 * Register plugin hooks.
	 */
	protected function __construct() {
		add_filter( 'rwd_image_sizes', array( $this, 'rmd_image_sizes' ) );
	}

	/**
	 * Load sizes from config file.
	 *
	 * @param array $image_sizes Image sizes passed to hook.
	 *
	 * @return array
	 */
	public function rmd_image_sizes( $image_sizes ) {
		$config_files = array(
			get_stylesheet_directory() . '/config/just-responsive-images.php',
			get_template_directory() . '/config/just-responsive-images.php',
		);
		foreach ( $config_files as $file ) {
			if ( is_file( $file ) ) {
				return include $file;
			}
		}
		return array();
	}
}
