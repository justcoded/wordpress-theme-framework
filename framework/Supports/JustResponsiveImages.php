<?php
namespace JustCoded\ThemeFramework\Supports;

/**
 * Class JustResponsiveImages
 *
 * Load rwd sizes from /config/image-sizes.php file inside the theme.
 *
 * @package JustCoded\ThemeFramework\Supports
 */
class JustResponsiveImages {

	/**
	 * JustResponsiveImages constructor.
	 *
	 * Register plugin hooks.
	 */
	public function __construct() {
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
	}
}