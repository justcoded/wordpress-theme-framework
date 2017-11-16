<?php
namespace JustCoded\ThemeFramework\Admin;

/**
 * Theme Settings base class
 * Contain few methods to work with Titan framework
 */
abstract class ThemeSettings {
	/**
	 * Unique framework instance to be used
	 *
	 * @var \TitanFramework
	 */
	protected static $tf;

	/**
	 * Theme Settings constructor
	 * init framework hook
	 */
	public function __construct() {
		add_action( 'tf_create_options', array( $this, 'init' ) );
	}

	/**
	 * Check and init framework instance if needed
	 */
	public static function check_instance() {
		if ( ! self::$tf ) {
			self::$tf = \TitanFramework::getInstance( 'just_theme_options' );
		}
	}

	/**
	 * Get theme setting from the database
	 *
	 * @param string $option_name  option name to get value from.
	 *
	 * @return mixed
	 */
	public static function get( $option_name ) {
		self::check_instance();

		return self::$tf->getOption( $option_name );
	}

	/**
	 * Run tabs methods one by one
	 * Search for function self::register{ucfirst(slug)}Tab
	 *
	 * @param \TitanFrameworkAdminPage $panel Titan framework panel object.
	 * @param array                    $tabs  Tabs init callbacks to be executed.
	 *
	 * @throws \Exception Missing method to execute.
	 */
	public function add_panel_tabs( $panel, $tabs ) {
		foreach ( $tabs as $slug => $name ) {
			$method = 'register' . ucfirst( $slug ) . 'Tab';
			if ( ! method_exists( $this, $method ) ) {
				throw new \Exception( 'ThemeSettings: Unable to find tab method "' . $method . '"' );
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
