<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Fields;

/**
 * Class Just_Field_Post
 */
class Field_Select_Posts extends \SiteOrigin_Widget_Field_Base {

	/**
	 * An array of post types to use in the autocomplete query. Only used for posts.
	 *
	 * @access protected
	 * @var array
	 */
	protected $post_types;

	/**
	 * @param mixed $value
	 * @param array $instance
	 *
	 * @return mixed|void
	 */
	protected function render_field( $value, $instance ) {
		// TODO: print selected values.
		?>
		<select id="<?php esc_attr( $this->element_id ); ?>" name="<?php esc_attr( $this->element_name ); ?>[]"
				class="widefat jc-widget-field-select-posts"
				multiple="multiple"
				data-post_types="post"
		>
			<option value="first" selected>First</option>
		</select>
		<?php
	}

	/**
	 * @param mixed $value
	 * @param array $instance
	 *
	 * @return array|mixed
	 */
	protected function sanitize_field_input( $value, $instance ) {
		// TODO: finalize this method.
		$values          = is_array( $value ) ? $value : array( $value );
		$keys            = array_keys( $this->options );
		$sanitized_value = array();
		foreach ( $values as $value ) {
			if ( ! in_array( $value, $keys ) ) {
				$sanitized_value[] = isset( $this->default ) ? $this->default : false;
			} else {
				$sanitized_value[] = $value;
			}
		}

		return count( $sanitized_value ) == 1 ? $sanitized_value[0] : $sanitized_value;
	}

	/**
	 * Enqueue needed js.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'pagebuilder-widget-field-select-posts',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/js/select-posts.js',
			array( 'jquery', 'select2' ),
			'1.0'
		);
	}

	/**
	 * Action to handle searching posts
	 */
	public static function ajax_search_posts() {
		if ( empty( $_REQUEST['_widgets_nonce'] )
			|| ! wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' )
		) {
			wp_die( __( 'Invalid request.', 'so-widgets-bundle' ), 403 );
		}

		global $wpdb;

		$filter = '';
		$params = array(
			'%' . $wpdb->esc_like( $_REQUEST['term'] ) . '%',
		);
		if ( ! empty( $_REQUEST['types'] ) ) {
			$registered_cpt = (array) get_post_types( array(
				'public' => true,
			) );
			$post_types     = array_intersect( explode( ',', $_REQUEST['types'] ), $registered_cpt );
			if ( ! empty( $post_types ) ) {
				$filter .= 'AND post_type IN (' . trim( str_repeat( '%s,', count( $post_types ) ), ',' ) . ')';
				$params = array_merge( $params, $post_types );
			}
		}

		$rows = $wpdb->get_results( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->posts}
			WHERE
				post_status = 'publish'
				AND post_title LIKE %s
				{$filter}
			ORDER BY post_modified DESC
			LIMIT 20
		", $params ), ARRAY_A );

		$results = [];
		foreach ( $rows as $row ) {
			$results[] = [
				'id'   => $row['ID'],
				'text' => $row['post_title'] . " ({$row['post_type']})",
			];
		}

		wp_send_json( array( 'results' => $results ) );
	}
}
