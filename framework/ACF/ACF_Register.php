<?php

namespace JustCoded\WP\Framework\ACF;


use StoutLogic\AcfBuilder\FieldBuilder;
use StoutLogic\AcfBuilder\FieldsBuilder;
use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class ACF_Register
 *
 * @property FieldBuilder[] $fields
 */
abstract class ACF_Register {
	use Singleton;
	use Has_ACF_Fields;

	/**
	 * ACF_Register constructor.
	 * - run init method to set fields configuration.
	 * - define acf hook to register fields
	 */
	protected function __construct() {
		$this->init();

		// init ACF hook for register fields.
		add_action( 'acf/init', array( $this, 'register' ) );
	}

	/**
	 * Init fields configuration method
	 *
	 * @return void
	 */
	abstract public function init();

	/**
	 * Register fields with ACF functions.
	 */
	public function register() {
		foreach ( $this->fields as $field ) {
			acf_add_local_field_group( $field->build() );
		}
	}

	/**
	 * Remove standard editor from post edit screen.
	 *
	 * @param string $post_type Post type ID to remove editor from.
	 */
	protected function remove_content_editor( $post_type ) {
		add_action( 'init', function () use ( $post_type ) {
			remove_post_type_support( $post_type, 'editor' );
		} );
	}

	/**
	 * ACF add_options_page function wrapper to check for exists.
	 *
	 * @param string $name Page name.
	 *
	 * @return bool
	 */
	public function add_options_page( $name ) {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( $name );

			return true;
		}

		return false;
	}
}
