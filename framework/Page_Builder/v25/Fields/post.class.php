<?php

/**
 * Class Just_Field_Post
 */
class Just_Field_Post extends SiteOrigin_Widget_Field_Autocomplete {

	/**
	 * An array of post types to use in the autocomplete query. Only used for posts.
	 *
	 * @access protected
	 * @var array
	 */
	protected $post_type;

	/**
	 * The CSS classes to be applied to the rendered text input.
	 */
	protected function get_input_classes() {
		return array( 'widefat', 'siteorigin-widget-input', 'siteorigin-widget-post-input' );
	}

	protected function get_default_options() {
		$defaults           = parent::get_default_options();
		$defaults['source'] = 'posts';

		return $defaults;
	}

	protected function render_after_field( $value, $instance ) {
		$post_type = $this->post_type;
		?>
		<div class="existing-content-selector">

			<input type="text" class="content-text-search"
				   data-types="<?php echo esc_attr( $post_type ) ?>"
				   data-source="<?php echo esc_attr( $this->source ) ?>"
				   placeholder="<?php esc_attr_e( 'Search', 'so-widgets-bundle' ) ?>"/>
			<ul class="items"></ul>

			<div class="buttons">
				<a href="#" class="button-close button"><?php esc_html_e( 'Close', 'so-widgets-bundle' ) ?></a>
			</div>
		</div>
		<?php
	}

	function enqueue_scripts() {
		wp_enqueue_script( 'post-js', get_template_directory_uri() . '/app/Page_Builder/Fields/js/post-field.js', array( 'jquery' ), '4.0.1.1' );
	}

	/**
	 * Action to handle searching posts
	 */
	static function action_search_posts() {
		if ( empty( $_REQUEST['_widgets_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' ) ) {
			wp_die( __( 'Invalid request.', 'so-widgets-bundle' ), 403 );
		}

		// Get all public post types, besides attachments
		$post_type = (array) get_post_types( array(
			'public' => true,
		) );

		if ( ! empty( $_REQUEST['postTypes'] ) ) {
			$post_type             = array_intersect( explode( ',', $_REQUEST['postTypes'] ), $post_type );
			$_REQUEST['postTypes'] = '';

		} else {
			unset( $post_type['attachment'] );
		}

		$post_type = $_REQUEST['types'];


		$post_type = apply_filters( 'siteorigin_widgets_search_posts_post_types', $post_type );

		global $wpdb;
		if ( ! empty( $post_type ) ) {
			$query = "AND post_type LIKE '%" . esc_sql( $post_type ) . "%'";
		} else {
			$query = '';
		}

		$results = $wpdb->get_results( "
		SELECT ID AS 'value', post_title AS label, post_type AS 'type'
		FROM {$wpdb->posts}
		WHERE
			post_status = 'publish' {$query}
		ORDER BY post_modified DESC
		LIMIT 20
	", ARRAY_A );

		wp_send_json( apply_filters( 'siteorigin_widgets_search_posts_results', $results ) );
	}
}
