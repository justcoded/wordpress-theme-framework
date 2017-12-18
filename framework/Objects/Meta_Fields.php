<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Meta_Fields is the base class for acquiring data from custom fields.
 */

class Meta_Fields {

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
	 * Current $post object
	 *
	 * @var \WP_Post
	 */
	public $post;

	/**
	 * Current $term object
	 *
	 * @var \WP_Term
	 */
	public $term;

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
		if ( $this->post ) {
			// getter for magic property $this->field_*.
			return $this->get_field( $name );
		} else {
			return $this->get_term_field( $name );
		}

		// trying to find get_{property} method.
		$getter = 'get_' . $name;
		if ( method_exists( $this, $getter ) ) {
			return $this->$getter();
		} else {
			throw new \Exception( 'Getting unknown property: ' . get_class( $this ) . '::' . $name );
		}
	}

	/**
	 * Sets value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 *
	 * @param string $name The property name or the event name.
	 * @param mixed  $value The property value.
	 *
	 * @throws \Exception Property is not defined or read-only.
	 * @see __get()
	 */
	public function __set( $name, $value ) {
		$setter = 'set_' . $name;
		if ( method_exists( $this, $setter ) ) {
			$this->$setter( $value );
		} elseif ( method_exists( $this, 'get_' . $name ) ) {
			throw new \Exception( 'Setting read-only property: ' . get_class( $this ) . '::' . $name );
		} else {
			throw new \Exception( 'Setting unknown property: ' . get_class( $this ) . '::' . $name );
		}
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
		$getter = 'get_' . $name;
		if ( method_exists( $this, $getter ) ) {
			return $this->$getter() !== null;
		} else {
			return false;
		}
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
	protected function get_field_jcf( $field_name, $post_id, $format_value ) {
		// Fix for getter from magic property field_*.
		if ( strpos( $field_name, '_' ) !== 0 ) {
			$field_name = "_{$field_name}";
		}
		return get_post_meta( $post_id, $field_name, $format_value );
	}

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
	protected function get_field_acf( $field_name, $post_id, $format_value ) {
		return get_field( $field_name, $post_id, $format_value );
	}

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

		// Use $this->post in case ID is empty.
		if ( empty( $post_id ) && ! empty( $this->post ) ) {
			$post_id = $this->post->ID;
		}

		// Check cache, if not exists - get field value.
		if ( ! isset( $this->_fields[ $post_id ][ $field_name ] ) ) {
			if ( 'just-custom-fields' === $this->custom_fields_plugin ) {
				$value = $this->get_field_jcf( $field_name, $post_id, $format_value );
			} elseif ( 'advanced-custom-fields' === $this->custom_fields_plugin ) {
				$value = $this->get_field_acf( $field_name, $post_id, $format_value );
			} else {
				throw new \Exception( get_class( $this ) . "::get_field() : Unsupported custom fields plugin \"{$this->custom_fields_plugin}\"" );
			}

			$this->_fields[ $post_id ][ $field_name ] = $value;
		}

		return $this->_fields[ $post_id ][ $field_name ];
	}

	/**
	 * Main term meta fields getter function.
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $term_id Term ID
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	protected function get_term_field( $field_name, $term_id = null, $format_value = true ) {

		// Use $this->term in case term_id is empty.
		if ( empty( $term_id ) && ! empty( $this->term ) ) {
			$term_id = $this->term->term_id;
		}

		// Check cache, if not exists - get field value.
		if ( ! isset( $this->_fields[ $term_id ][ $field_name ] ) ) {
			if ( 'just-custom-fields' === $this->custom_fields_plugin ) {
				$value = $this->get_term_field_jcf( $field_name, $term_id, $format_value );
			} elseif ( 'advanced-custom-fields' === $this->custom_fields_plugin ) {
				$value = $this->get_term_field_acf( $field_name, $term_id );
			} else {
				throw new \Exception( get_class( $this ) . "::get_field() : Unsupported custom fields plugin \"{$this->custom_fields_plugin}\"" );
			}

			$this->_fields[ $term_id ][ $field_name ] = $value;
		}

		return $this->_fields[ $term_id ][ $field_name ];
	}

	/**
	 * Getter of termmeta from just custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $term_id Term ID
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	protected function get_term_field_jcf( $field_name, $term_id, $format_value ) {
		// Fix for getter from magic property field_*.
		if ( strpos( $field_name, '_' ) !== 0 ) {
			$field_name = "_{$field_name}";
		}
		return get_term_meta( $term_id, $field_name, $format_value );
	}

	/**
	 * Getter of term field data from wp options for advanced custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $post_id Term ID
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	protected function get_term_field_acf( $field_name, $term_id ) {
		if ( $term_id ) {
			$option_name = 'category_' . $term_id . '_' . $field_name;
			return get_option( $option_name );
		} else {
			return false;
		}
	}

}
