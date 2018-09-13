<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Class Thememeta
 * Get theme option fields
 */
class Thememeta extends Meta {

	/**
	 * Get post id
	 *
	 * @return false|int
	 */
	public function get_object_id() {
		return 'option';
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
		if ( class_exists( 'TitanFramework' ) ) {
			$options = \TitanFramework::getInstance( 'just_theme_options' );

			return $options->getOption( $field_name );
		}
	}

}
