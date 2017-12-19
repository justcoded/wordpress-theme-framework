<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Meta is the base class for acquiring data from custom fields.
 */

abstract class Meta {

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
	 * JCF Plugin name
	 */
	const PLUGIN_JCF = 'just-custom-fields';

	/**
	 * ACF Plugin name
	 */
	const PLUGIN_ACF = 'advanced-custom-fields';

	/**
	 * Current page/term object. It can be WP_Post or WP_Term
	 *
	 * @var object
	 */
	public $object;

	/**
	 * Meta constructor.
	 */
	public function __construct() {
		if ( class_exists('acf') ) {
			$this->custom_fields_plugin = self::PLUGIN_ACF;
		} else {
			$this->custom_fields_plugin = self::PLUGIN_JCF;
		}

		return $this->custom_fields_plugin;
	}

	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 *
	 * @param string $name The property name.
	 *
	 * @return mixed the property value
	 * @throws \Exception Property is not defined.
	 * @see __set()
	 */
	public function __get( $name ) {

		return $this->get_field( $name );
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

		return null !== $this->get_field($name);
	}

	/**
	 * Sets an object property to null.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($object->property)`.
	 *
	 * Note that if the property is not defined, this method will do nothing.
	 * If the property is read-only, it will throw an exception.
	 *
	 * @param string $name The property name.
	 *
	 * @throws \Exception The property is read only.
	 */
	public function __unset( $name ) {
		$setter = 'set_' . $name;
		if ( method_exists( $this, $setter ) ) {
			$this->$setter( null );
		} elseif ( method_exists( $this, 'get_' . $name ) ) {
			throw new \Exception( 'Setting read-only property: ' . get_class( $this ) . '::' . $name );
		}
	}

	/**
	 * Getter of postmeta from just custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $post_id Post ID if different from get_the_ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	abstract public function get_field_jcf( $field_name, $post_id, $format_value );

	/**
	 * Getter of postmeta from advanced custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $post_id Post ID if different from get_the_ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	abstract public function get_field_acf( $field_name, $post_id, $format_value );

	/**
	 * Main post meta fields getter function.
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $post_id Post ID if different from get_the_ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_field( $field_name, $post_id = null, $format_value = true ) {

		if ( is_a( $this->object, 'WP_Post' ) ) {
			$post_id = $this->object->ID;
		} else {
			$post_id = $this->object->term_id;
		}

		// Check cache, if not exists - get field value.
		if ( ! isset( $this->_fields[ $post_id ][ $field_name ] ) ) {
			if ( self::PLUGIN_JCF === $this->custom_fields_plugin ) {
				$value = $this->get_field_jcf( $field_name, $post_id, $format_value );
			} elseif ( self::PLUGIN_ACF === $this->custom_fields_plugin ) {
				$value = $this->get_field_acf( $field_name, $post_id, $format_value );
			} else {
				throw new \Exception( get_class( $this ) . "::get_field() : Unsupported custom fields plugin \"{$this->custom_fields_plugin}\"" );
			}

			$this->_fields[ $post_id ][ $field_name ] = $value;
		}

		return $this->_fields[ $post_id ][ $field_name ];
	}

}
