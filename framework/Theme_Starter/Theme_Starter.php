<?php
/**
 * Theme Starter Class.
 */

namespace JustCoded\WP\Framework\Theme_Starter;

use JustCoded\WP\Framework\Autoload;
use JustCoded\WP\Framework\Objects\Singleton;

class Theme_Starter {

	use Singleton;

	/**
	 * @var bool - If theme is created.
	 */
	protected $theme_created = true;

	/**
	 * @var string - Namespace of a class that needs to be autoloaded.
	 */
	protected $namespace = '';

	/**
	 * Method for starting theme.
	 *
	 * @param $namespace
	 */
	public function start_theme( $namespace ) {

		$this->set_namespace( $namespace );

		try {
			$this->register_autoload();
			$this->create_theme_instance();
		} catch ( \Exception $exception ) {
			$this->theme_created = false;
			echo $exception->getMessage();
			wp_die();
		}
	}

	/**
	 * Method registers autoload for current theme.
	 *
	 * @throws \Exception - Exception on failure
	 * @return bool - True on success.
	 */
	protected function register_autoload() {
		$theme_namespace = $this->namespace . '\Theme';
		if ( ! new Autoload( $theme_namespace, get_template_directory() . '/app' ) ) {
			throw new \Exception( 'Could not autoload theme with ' . $theme_namespace . ' namespace' );
		} else {
			return true;
		}
	}

	/**
	 * Creating Theme Instance.
	 */
	protected function create_theme_instance() {
		$class_name = "{$this->namespace}\\Theme\\Theme";
		if ( class_exists( $class_name ) ) {
			$class_name::instance();
		} else {
			throw new \Exception( 'Class ' . $class_name . " could not be found" );
		}
	}

	/**
	 * Setting theme namespace value.
	 *
	 * @param $namespace
	 */
	protected function set_namespace( $namespace ) {
		$this->namespace = $namespace;
	}

	public function is_theme_created() {
		return $this->theme_created;
	}

}