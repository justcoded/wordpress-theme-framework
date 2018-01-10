<?php
namespace JustCoded\WP\Framework\Admin;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Theme Settings base class
 * Contain few methods to work with Titan framework
 */
abstract class Theme_Settings {
	use Singleton;

	/**
	 * Unique framework instance to be used
	 *
	 * @var \TitanFramework
	 */
	protected static $titan_instance;

	/**
	 * Theme Settings constructor
	 * init framework hook
	 */
	protected function __construct() {
		add_action( 'tf_create_options', array( $this, 'init' ) );
	}

	/**
	 * Check and create titan framework instance if needed
	 *
	 * @return \TitanFramework
	 */
	public static function titan_instance() {
		if ( ! static::$titan_instance ) {
			static::$titan_instance = \TitanFramework::getInstance( 'just_theme_options' );
		}
		return static::$titan_instance;
	}

	/**
	 * Check and init framework instance if needed
	 *
	 * @deprecated from 2.1.2, use titan_instance() method.
	 */
	public static function check_instance() {
		return static::titan_instance();
	}

	/**
	 * Get theme setting from the database
	 *
	 * @param string $option_name  option name to get value from.
	 *
	 * @return mixed
	 */
	public static function get( $option_name ) {
		return static::titan_instance()->getOption( $option_name );
	}

	/**
	 * Run tabs methods one by one
	 * Search for function self::register_{slug}_tab
	 *
	 * @param \TitanFrameworkAdminPage $panel Titan framework panel object.
	 * @param array                    $tabs  Tabs init callbacks to be executed.
	 *
	 * @deprecated from 1.1.2
	 * @throws \Exception Missing method to execute.
	 */
	public function add_panel_tabs( $panel, $tabs ) {
		foreach ( $tabs as $slug => $name ) {
			$method = 'register_' . $slug . '_tab';
			if ( ! method_exists( $this, $method ) ) {
				throw new \Exception( 'Theme_Settings: Unable to find tab method "' . $method . '"' );
			}

			$this->$method( $panel );
		}
	}

	/**
	 * Main function to init Theme settings
	 * should be written in child App class
	 *
	 * @return mixed
	 */
	abstract public function init();

}
