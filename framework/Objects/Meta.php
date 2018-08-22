<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Meta is the base class for acquiring data from custom fields.
 */
abstract class Meta {

	/**
	 * JCF Plugin name
	 */
	const PLUGIN_JCF = 'just-custom-fields';

	/**
	 * ACF Plugin name
	 */
	const PLUGIN_ACF = 'advanced-custom-fields';

	/**
	 * Object with meta data.
	 *
	 * @var \WP_Post|\WP_Term
	 */
	public $object;

	/**
	 * Plugin used for extending of post custom fields.
	 *
	 * Available values:
	 *      'just-custom-fields'
	 *      'advanced-custom-fields'
	 *
	 * @var string
	 */
	public $custom_fields_plugin = 'just-custom-fields';

	/**
	 * Internal cache for object meta data
	 *
	 * @var array
	 */
	protected static $_meta = [];

	/**
	 * Meta constructor.
	 */
	public function __construct() {
		if ( class_exists( 'acf' ) ) {
			$this->custom_fields_plugin = self::PLUGIN_ACF;
		} else {
			$this->custom_fields_plugin = self::PLUGIN_JCF;
		}
	}

	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 *
	 * @param string $name a property name.
	 *
	 * @return mixed
	 * @throws \Exception Unable to get property.
	 */
	public function __get( $name ) {
		return $this->get_value( $name );
	}

	/**
	 * Checks if the named property is set (not null).
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($object->property)`.
	 *
	 * Note that if the property is not defined, false will be returned.
	 *
	 * @param string $name The property name or the event name.
	 *
	 * @return boolean Whether the named property is set (not null).
	 */
	public function __isset( $name ) {
		return null !== $this->get_value( $name );
	}

	/**
	 * Get object id
	 * It can be post or term
	 *
	 * @return mixed
	 */
	abstract public function get_object_id();

	/**
	 * Getter of postmeta from just custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $object_id Post/Term ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	abstract public function get_value_jcf( $field_name, $object_id, $format_value );

	/**
	 * Getter of postmeta from advanced custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $object_id Post/Term ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	abstract public function get_value_acf( $field_name, $object_id, $format_value );

	/**
	 * Main post meta fields getter function.
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $object_id Post ID if different from get_the_ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_value( $field_name, $object_id = null, $format_value = true ) {

		if ( empty( $object_id ) ) {
			$object_id = $this->get_object_id();
		}

		// Check cache, if not exists - get meta value.
		if ( ! isset( $this->_meta[ $object_id ][ $field_name ] ) ) {
			if ( self::PLUGIN_JCF === $this->custom_fields_plugin ) {
				$value = $this->get_value_jcf( $field_name, $object_id, $format_value );
			} elseif ( self::PLUGIN_ACF === $this->custom_fields_plugin ) {
				$value = $this->get_value_acf( $field_name, $object_id, $format_value );
			} else {
				throw new \Exception( get_class( $this ) . "::get_value() : Unsupported custom fields plugin \"{$this->custom_fields_plugin}\"" );
			}

			static::$_meta[ $object_id ][ $field_name ] = $value;
		}

		return static::$_meta[ $object_id ][ $field_name ];
	}

}
