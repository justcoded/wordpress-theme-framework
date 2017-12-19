<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Get fields from terms
 *
 * Class Termmeta
 *
 * @package JustCoded\WP\Framework\Objects
 */
class Termmeta extends Meta {

	/**
	 * Internal cache for post custom fields data
	 *
	 * @var array
	 */
	protected $_fields = [];

	/**
	 * Postmeta constructor.
	 */
	public function __construct() {
		parent::__construct();

		// set current post for new created instance.
		$this->set_term( null );
	}

	/**
	 * Set $term property correctly
	 *
	 * @param \WP_Term|int|null $term Term object, id or null to take current object.
	 */
	protected function set_term( $term = null ) {
		if ( is_null( $term ) ) {
			$term = get_queried_object();
		}
		$this->object = $term;
	}

	/**
	 * Getter of postmeta from advanced custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $term_id Term ID
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_field_acf( $field_name, $term_id, $format_value ) {
		if ( $term_id ) {
			$option_name = 'category_' . $term_id . '_' . $field_name;
			return get_option( $option_name );
		} else {
			return false;
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
	public function get_field_jcf( $field_name, $term_id, $format_value ) {
		// Fix for getter from magic property field_*.
		if ( strpos( $field_name, '_' ) !== 0 ) {
			$field_name = "_{$field_name}";
		}
		return get_term_meta( $term_id, $field_name, $format_value );
	}

}
