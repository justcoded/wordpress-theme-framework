<?php
namespace JustCoded\WP\Framework;

/**
 * SPL autoload registration for theme to prevent using file includes
 */
class Autoload {
	/**
	 * Namespace prefix in PSR-4 format (Vendor/Module)
	 *
	 * @var string
	 */
	protected $app_namespace;

	/**
	 * Directory name path to search classes
	 *
	 * @var string
	 */
	protected $app_path;

	/**
	 * Class constructor
	 * register SPL autoload callback functions for App and framework
	 *
	 * @param string $app_namespace App namespace.
	 * @param string $app_path App directory path.
	 */
	public function __construct( $app_namespace, $app_path ) {
		$this->app_namespace = $app_namespace;
		$this->app_path      = $app_path;

		spl_autoload_register( array( $this, 'spl_autoload' ) );
	}

	/**
	 * Search for the class by app namespace
	 *
	 * @param string $class_name  not loaded class name.
	 */
	public function spl_autoload( $class_name ) {
		$this->load_class( $class_name, $this->app_namespace, $this->app_path );
	}

	/**
	 * Search for the class by $namespace and include it from $path if found.
	 *
	 * @param string $class_name  not loaded class name.
	 * @param string $namespace   namespace in PSR-4 format.
	 * @param string $dir_path    path to search files.
	 */
	protected function load_class( $class_name, $namespace, $dir_path ) {
		// check if this class is related to the plugin namespace. exit if not.
		if ( strpos( $class_name, $namespace ) !== 0 ) {
			return;
		}

		$class_path = substr( $class_name, strlen( $namespace ) );
		$class_path = str_replace( '\\', '/', $class_path );

		$path = $dir_path . '/' . trim( $class_path, '/' ) . '.php';

		if ( is_file( $path ) ) {
			require_once $path;
		}
	}
}
