<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Class Postmeta
 * Get meta fields from posts
 */
class Postmeta extends Meta {

	/**
	 * Current Object to get meta data from.
	 *
	 * @var \WP_Post
	 */
	public $object;

	/**
	 * Postmeta constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->the_post();
	}

	/**
	 * Set $object property correctly
	 *
	 * @param \WP_Post|int|null $post Post object, id or null to take current object.
	 */
	public function the_post( $post = null ) {
		if ( is_null( $post ) ) {
			$post = get_the_ID();
		}
		$this->object = get_post( $post );
	}

	/**
	 * Get post id
	 *
	 * @return false|int
	 */
	public function get_object_id() {
		return $this->object->ID;
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
	public function get_value_acf( $field_name, $post_id, $format_value ) {
		return get_field( $field_name, $post_id, $format_value );
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
	public function get_value_jcf( $field_name, $post_id, $format_value ) {
		// Fix for getter from magic property _*.
		if ( strpos( $field_name, '_' ) !== 0 ) {
			$field_name = "_{$field_name}";
		}
		return get_post_meta( $post_id, $field_name, $format_value );
	}

}
