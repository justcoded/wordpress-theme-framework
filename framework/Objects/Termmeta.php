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
	 * Current Object to get meta data from.
	 *
	 * @var \WP_Term
	 */
	public $object;

	/**
	 * Postmeta constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->the_term( get_queried_object_id() );
	}


	/**
	 * Set $object property correctly
	 *
	 * @param \WP_Term|int|null $term Term object, id or null to take current object.
	 */
	public function the_term( $term ) {
		$this->object = get_term( $term );
	}

	/**
	 * Get post id
	 *
	 * @return false|int
	 */
	public function get_object_id() {
		return $this->object->term_id;
	}

	/**
	 * Getter of postmeta from advanced custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $term_id Term ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_value_acf( $field_name, $term_id, $format_value ) {
		$term = ( $term_id === $this->get_object_id() ) ? $this->object : get_term( $term_id );
		return get_field( $field_name, $term );
	}

	/**
	 * Getter of termmeta from just custom fields
	 *
	 * @param string      $field_name Field name to get.
	 * @param int         $term_id Term ID.
	 * @param bool|string $format_value Format value or not.
	 *
	 * @return mixed
	 * @throws \Exception Unsupported custom fields plugin.
	 */
	public function get_value_jcf( $field_name, $term_id, $format_value ) {
		// Fix for getter from magic property field_*.
		if ( strpos( $field_name, '_' ) !== 0 ) {
			$field_name = "_{$field_name}";
		}
		return get_term_meta( $term_id, $field_name, $format_value );
	}

}
