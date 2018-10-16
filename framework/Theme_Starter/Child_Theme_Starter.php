<?php
/**
 * Child Theme Starter Class.
 */

namespace JustCoded\WP\Framework\Theme_Starter;

use JustCoded\WP\Framework\Autoload;
use JustCoded\WP\Framework\Objects\Singleton;
use Symfony\Component\Config\Definition\Exception\Exception;

class Child_Theme_Starter extends Theme_Starter {

	/**
	 * @var string $theme_namespace - Child theme namespace
	 */
	private $theme_namespace;


	/**
	 * Method registers autoload for current theme.
	 *
	 * @return bool|Exception - Exception on failure and true on success.
	 */
	protected function register_autoload() {
		$child_theme_namespace = $this->theme_namespace . '\Child\Theme';
		$theme_namespace       = $this->theme_namespace . '\Theme';
		if ( new Autoload( $theme_namespace, get_template_directory() . '/app' ) ) {
			if ( new Autoload( $child_theme_namespace, get_stylesheet_directory() . '/app' ) ) {
				return true;
			} else {
				throw new Exception( 'Could not autoload child theme with ' . $child_theme_namespace . ' namespace' );
			}
		} else {
			throw new Exception( 'Could not autoload theme with ' . $theme_namespace . ' namespace' );
		}
	}

	/**
	 * Creating Theme Instance.
	 */
	protected function create_theme_instance() {
		if ( ! $this->theme_created ) {
			throw new \Exception( 'Could not autoload ' . $this->theme_namespace . '\Child\Theme\Theme parent class' );
		}
		$child_class_name = "{$this->theme_namespace}\\Child\\Theme\\Theme";
		if ( class_exists( $child_class_name ) && class_exists( $child_class_name ) ) {
			$child_class_name::instance();
		} else {
			throw new \Exception( 'Class ' . $child_class_name . ' could not be found' );
		}
	}

	/**
	 * Setting theme namespace value.
	 *
	 * @param $theme_namespace - Child theme namespace.
	 */
	protected function set_namespace( $theme_namespace ) {
		$this->theme_namespace = $theme_namespace;
	}

}