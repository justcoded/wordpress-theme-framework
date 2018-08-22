<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Fields;

/**
 * Class Just_Field_Post
 */
class Field_Select_Posts extends \SiteOrigin_Widget_Field_Base {

	/**
	 * An array of post types to use in the autocomplete query. Only used for posts.
	 *
	 * @var array
	 */
	protected $post_types = '';

	/**
	 * Render field HTML
	 *
	 * @param mixed $value Widget values.
	 * @param array $instance Widget instance.
	 *
	 * @return mixed|void
	 */
	protected function render_field( $value, $instance ) {
		$posts = array();
		if ( ! empty( $value ) ) {
			$posts = get_posts( [
				'post__in'            => $value,
				'ignore_sticky_posts' => true,
			] );
		}
		?>
		<select name="<?php echo esc_attr( $this->element_name ); ?>" id="<?php echo esc_attr( $this->element_id ); ?>"
				class="widefat jc-widget-field-select-posts"
				multiple="multiple"
				data-post_types="<?php echo esc_attr( $this->post_types ); ?>"
		>
			<?php if ( ! empty( $posts ) ) : ?>
				<?php foreach ( $posts as $post ) : ?>
				<option value="<?php echo esc_attr( $post->ID ); ?>" selected="selected">
					<?php echo esc_html( $this->get_post_caption( $post ) ); ?>
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
				$filter .= ' AND post_type IN (' . trim( str_repeat( '%s,', count( $post_types ) ), ',' ) . ')';
				$params  = array_merge( $params, $post_types );
			}
		}

		if ( ! empty( $_REQUEST['selected'] ) ) {
			$filter .= ' AND `ID` NOT IN (' . trim( str_repeat( '%d,', count( $_REQUEST['selected'] ) ), ',' ) . ')';
			$params  = array_merge( $params, $_REQUEST['selected'] );
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
		", $params ), OBJECT_K );

		$results = [];
		foreach ( $rows as $row ) {
			$results[] = [
				'id'   => $row->ID,
				'text' => self::get_post_caption( $row ),
			];
		}

		wp_send_json( array( 'results' => $results ) );
		exit;
	}

	/**
	 * Generate caption to display in select box
	 *
	 * @param \WP_Post $post Post to generate caption for.
	 *
	 * @return string
	 */
	protected static function get_post_caption( $post ) {
		return "$post->post_title ({$post->post_type})";
	}
}
