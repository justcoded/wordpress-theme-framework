<?php

namespace JustCoded\WP\Framework\ACF;

use JustCoded\WP\Framework\Objects\Singleton;
use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class ACF_Definition
 *
 * @property FieldsBuilder[] $fields
 */
abstract class ACF_Definition {
	use Singleton;
	use Has_ACF_Fields;

	/**
	 * ACF_Definition constructor.
	 * Run init method to set fields configuraiton.
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Init fields configuration method
	 *
	 * @return void
	 */
	abstract public function init();

	/**
	 * Magic getter to get some field registered.
	 *
	 * @param string $name Field name.
	 *
	 * @return FieldsBuilder
	 * @throws \InvalidArgumentException
	 */
	public function __get( $name ) {
		if ( isset( $this->fields[ $name ] ) ) {
			return $this->fields[ $name ];
		} else {
			$self = static::instance();
			throw new \InvalidArgumentException( get_class( $self ) . ": Field definition missing for \"{$name}\"." );
		}
	}

	/**
	 * Static getter to get field registered
	 * by default return first field if name is not specified
	 *
	 * @param string|null $name Field name.
	 *
	 * @return FieldsBuilder
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public static function get( string $name = null ) {
		$self = static::instance();

		if ( empty( $self->fields ) ) {
			throw new \Exception( get_class( $self ) . '::get() - No fields registered.' );
		}
		if ( is_null( $name ) ) {
			$name = key( $self->fields );
		}

		// use magic getter to not duplicate code here.
		return $self->$name;
	}
}
