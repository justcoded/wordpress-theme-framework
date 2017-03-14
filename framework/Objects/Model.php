<?php
namespace JustCoded\ThemeFramework\Objects;

/**
 * Model is the base class for data models.
 * Model should implement data get/set operations.
 *
 * Base Model class implements basic "property" features for clean usage in views.
 *
 * A property is defined by a getter method (e.g. `getLabel`), and/or a setter method (e.g. `setLabel`). For example,
 * the following getter and setter methods define a property named `label`:
 *
 * ~~~
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ~~~
 *
 * Property names are *case-insensitive*.
 *
 * A property can be accessed like a member variable of an object. Reading or writing a property will cause the invocation
 * of the corresponding getter or setter method. For example,
 *
 * ~~~
 * // equivalent to $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');
 * $object->label = 'abc';
 * ~~~
 *
 * If a property has only a getter method and has no setter method, it is considered as *read-only*. In this case, trying
 * to modify the property value will cause an exception.
 */
class Model {
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
	 * Internal cache of wp queries
	 *
	 * @var \WP_Query[]
	 */
	private $_queries = [];

	/**
	 * Internal cache for post custom fields data
	 *
	 * @var array
	 */
	protected $_fields = [];

	/**
	 * Model constructor.
	 */
	public function __construct() {
		// set current post for new created instance.
		$this->set_post( null );
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
		// getter for magic property $this->field_*.
		if ( strpos( $name, 'field_' ) === 0 ) {
			$field_name = preg_replace( '/^field_/', '', $name );
			return $this->get_field( $field_name );
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
	 * Run query and remember it to cache
	 * In this way you can use magic getter withour reseting query
	 *
	 * @param array  $params Usual WP_Query params array.
	 * @param string $method Cache key.
	 *
	 * @return \WP_Query
	 */
	public function wp_query( $params, $method ) {
		if ( ! isset( $this->_queries[ $method ] ) ) {
			$this->_queries[ $method ] = new \WP_Query( $params );
		}

		return $this->_queries[ $method ];
	}

	/**
	 * Run query and remember it to cache
	 * In this way you can use magic getter withour reseting query
	 *
	 * @param array  $params Usual WP_Query params array.
	 * @param string $method Cache key.
	 *
	 * @return \WP_Query
	 */
	public function archive_query( $params, $method ) {
		if ( ! isset( $this->_queries[ $method ] ) ) {
			global $wp_query;
			$wp_query->is_single = false;

			$paged           = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$params['paged'] = $paged;

			$this->_queries[ $method ] = new \WP_Query( $params );
		}

		return $this->_queries[ $method ];
	}

	/**
	 * Clean up queries cache in case you need to run new query
	 */
	public function reset_queries() {
		$this->_queries = [];
	}

	/**
	 * Set $post property correctly
	 *
	 * @param \WP_Post|int|null $post Post object, id or null to take current object.
	 */
	public function set_post( $post = null ) {
		if ( is_null( $post ) ) {
			$post = get_the_ID();
		}
		$this->post = get_post( $post );
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
	 * Getter of postmeta from just custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $post_id Post ID if different from get_the_ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_field_jcf( $field_name, $post_id, $format_value ) {
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
	public function get_field_acf( $field_name, $post_id, $format_value ) {
		return get_field( $field_name, $post_id, $format_value );
	}
}
