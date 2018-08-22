<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Fields;

/**
 * Class Just_Field_Taxonomy
 */
class Field_Select_Terms extends \SiteOrigin_Widget_Field_Base {

	/**
	 * This field is responsible for filter by `taxonomy`.
	 *
	 * @var string
	 */
	protected $taxonomies;


	/**
	 * Render field HTML
	 *
	 * @param mixed $value Widget values.
	 * @param array $instance Widget instance.
	 *
	 * @return mixed|void
	 */
	protected function render_field( $value, $instance ) {
		$terms = array();
		if ( ! empty( $value ) ) {
			$terms = get_terms( [
				'include'    => $value,
				'hide_empty' => false,
			] );
		}
		?>
		<select name="<?php echo esc_attr( $this->element_name ); ?>" id="<?php echo esc_attr( $this->element_id ); ?>"
				class="widefat jc-widget-field-select-posts"
				multiple="multiple"
				data-taxonomies="<?php echo esc_attr( $this->taxonomies ); ?>"
		>
			<?php if ( ! empty( $terms ) ) : ?>
				<?php foreach ( $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>" selected="selected">
					<?php echo esc_html( $this->get_term_caption( $term ) ); ?>
				</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Sanitize the input received from their HTML form field.
	 *
	 * @param mixed $value Value from user input.
	 * @param array $instance Widget instance.
	 *
	 * @return array|mixed
	 */
	protected function sanitize_field_input( $value, $instance ) {
		$values = array();
		if ( ! is_null( $value ) ) {
			$values = is_array( $value ) ? $value : array( $value );
			$values = array_map( 'intval', $values );
		}

		return $values;
	}

	/**
	 * Enqueue needed js.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'pagebuilder-widget-field-select-terms',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/js/select-terms.js',
			array( 'jquery', 'select2' ),
			'1.0'
		);
	}

	/**
	 * Action to handle searching posts
	 */
	public static function ajax_search_terms() {
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
		if ( ! empty( $_REQUEST['taxonomies'] ) ) {
			$taxonomies = explode( ',', $_REQUEST['taxonomies'] );
			if ( ! empty( $taxonomies ) ) {
				$filter .= ' AND tax.taxonomy IN (' . trim( str_repeat( '%s,', count( $taxonomies ) ), ',' ) . ')';
				$params  = array_merge( $params, $taxonomies );
			}
		}

		if ( ! empty( $_REQUEST['selected'] ) ) {
			$filter .= ' AND `t`.`term_id` NOT IN (' . trim( str_repeat( '%d,', count( $_REQUEST['selected'] ) ), ',' ) . ')';
			$params  = array_merge( $params, $_REQUEST['selected'] );
		}

		$query = $wpdb->prepare( "
			SELECT t.term_id, t.name, tax.taxonomy
			FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tax ON tax.term_id = t.term_id
			WHERE
				t.name LIKE %s
				{$filter}
			ORDER BY t.name ASC
			LIMIT 20
		", $params );
		$rows  = $wpdb->get_results( $query, OBJECT_K );

		$results = [];
		foreach ( $rows as $row ) {
			$results[] = [
				'id'   => $row->term_id,
				'text' => self::get_term_caption( $row ),
			];
		}

		wp_send_json( array( 'results' => $results ) );
		exit;
	}

	/**
	 * Generate caption to display in select box
	 *
	 * @param \WP_Term $term Post to generate caption for.
	 *
	 * @return string
	 */
	protected static function get_term_caption( $term ) {
		return "$term->name ({$term->taxonomy})";
	}

}
