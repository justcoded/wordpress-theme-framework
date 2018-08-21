<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Fields;

/**
 * Class Just_Field_Post
 */
class Field_Select_Posts extends \SiteOrigin_Widget_Field_Text_Input_Base {

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

	/**
	 * Method is used to render html after field.
	 *
	 * @param mixed $value - Value.
	 * @param mixed $instance - Instance.
	 */
	protected function render_after_field( $value, $instance ) {
		$post_type = $this->post_type;
		?>
		<div class="existing-content-selector">

			<input type="text" class="content-text-search"
					data-types="<?php echo esc_attr( $post_type ); ?>"
					placeholder="<?php esc_attr_e( 'Search', 'so-widgets-bundle' ); ?>"/>
			<ul class="items"></ul>

			<div class="buttons">
				<a href="#" class="button-close button"><?php esc_html_e( 'Close', 'so-widgets-bundle' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue needed js.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'post-js',
			plugin_dir_url( JTF_PLUGIN_FILE ) . 'framework/Page_Builder/v25/Fields/js/post-field.js',
			array( 'jquery' ),
			'4.0.1.1'
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

		// Get all public post types, besides attachments.
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
