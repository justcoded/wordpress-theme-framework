<?php

/**
 * Class Just_Field_Taxonomy
 */
class Just_Field_Taxonomy extends SiteOrigin_Widget_Field_Autocomplete {

	/**
	 * This field is responsible for `taxonomy` field in SQL request.
	 *
	 * @access protected
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * The CSS classes to be applied to the rendered text input.
	 */
	protected function get_input_classes() {
		return array( 'widefat', 'siteorigin-widget-input', 'siteorigin-widget-taxonomy-input' );
	}

	protected function get_default_options() {
		$defaults             = parent::get_default_options();
		return $defaults;
	}

	protected function render_after_field( $value, $instance ) {

		$post_types = ! empty( $this->post_types ) && is_array( $this->post_types ) ? implode( ',', $this->post_types ) : '';
		if ( empty( $this->taxonomy ) ) {
			$this->taxonomy = $this->get_default_options()['taxonomy'];
		}

		if ( ! in_array( $this->taxonomy, get_taxonomies(), true ) && null !== $this->taxonomy ) {
			echo '<script>alert("No such taxonomy: ' . $this->taxonomy . '");</script>';

			return;
		}
		?>
		<div class="existing-content-selector">

			<input type="text" class="content-text-search"
				   data-post-types="<?php echo esc_attr( $post_types ) ?>"
				   data-source="<?php echo esc_attr( $this->source ) ?>"
				   data-taxonomy="<?php echo esc_attr( $this->taxonomy ); ?>"
				   placeholder="<?php esc_attr_e( 'Search', 'so-widgets-bundle' ) ?>"/>

			<ul class="items"></ul>

			<div class="buttons">
				<a href="#" class="button-close button"><?php esc_html_e( 'Close', 'so-widgets-bundle' ) ?></a>
			</div>
		</div>
		<?php
	}

	function enqueue_scripts() {
		wp_enqueue_script( 'taxonomy-js', get_template_directory_uri() . '/app/Page_Builder/Fields/js/taxonomy-field.js', array( 'jquery' ), '4.1' );
	}

	/**
	 * Action to handle searching taxonomy terms.
	 */
	static function my_search_terms() {
		if ( empty( $_REQUEST['_widgets_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' ) ) {
			wp_die( __( 'Invalid request.', 'so-widgets-bundle' ), 403 );
		}

		global $wpdb;
		$term     = ! empty( $_GET['term'] ) ? stripslashes( $_GET['term'] ) : '';
		$taxonomy = ! empty( $_REQUEST['taxonomy'] ) ? stripslashes( $_REQUEST['taxonomy'] ) : '';
		if ( $taxonomy ) {
			$query = $wpdb->prepare( "
		SELECT terms.slug AS 'value', terms.term_id AS 'term_id', terms.name AS 'label', termtaxonomy.taxonomy AS 'type'
		FROM $wpdb->terms AS terms
		JOIN $wpdb->term_taxonomy AS termtaxonomy ON terms.term_id = termtaxonomy.term_id
		WHERE
			termtaxonomy.taxonomy LIKE '%s'
		LIMIT 20
	", '%' . $taxonomy . '%' );
		} else {
			$query = $wpdb->prepare( "
		SELECT terms.slug AS 'value', terms.term_id AS 'term_id', terms.name AS 'label', termtaxonomy.taxonomy AS 'type'
		FROM $wpdb->terms AS terms
		JOIN $wpdb->term_taxonomy AS termtaxonomy ON terms.term_id = termtaxonomy.term_id
		LIMIT 20
	");
		}

		$term = trim( $term, '%' );


		$results = array();

		foreach ( $wpdb->get_results( $query ) as $result ) {
			$results[] = array(
				'value' => $result->value,
				'label' => $result->label,
				'type'  => $result->type,
				'id'    => $result->term_id,
			);
		}
		wp_send_json( $results );

	}

}
