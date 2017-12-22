<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Get fields from posts
 *
 * Class Postmeta
 *
 * @package JustCoded\WP\Framework\Objects
 */
class Postmeta extends Meta {

	/**
	 * Postmeta constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get post id
	 *
	 * @return false|int
	 */
	public function get_id() {

		return get_queried_object_id();
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

}
