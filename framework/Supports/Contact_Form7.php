<?php
namespace JustCoded\WP\Framework\Supports;

use JustCoded\WP\Framework\Objects\Post_Type;

/**
 * Class for save requests from plugin's Contact Form 7 forms
 */
class Contact_Form7 extends Post_Type {
	/**
	 * ID
	 *
	 * @var string
	 */
	public static $ID = 'form_request';

	/**
	 * Rewrite URL part
	 *
	 * @var string
	 */
	public static $SLUG = 'form-request';

	/**
	 * System fields excluded from render
	 *
	 * @var array
	 */
	public $system_fields = array(
		'_wpcf7',
		'_wpcf7_version',
		'_wpcf7_locale',
		'_wpcf7_unit_tag',
		'_wpnonce',
		'_wpcf7_is_ajax_call',
		'_edit_lock',
		'_edit_last',
		'request_uri',
		'_REMOTE_ADDR',
	);

	/**
	 * FormRequest constructor.
	 * define WordPress hooks
	 */
	protected function __construct() {
		parent::__construct();
		add_action( 'wpcf7_before_send_mail', array( $this, 'save_form_request' ) );

		add_filter( 'manage_form_request_posts_columns', array( $this, 'edit_grid_columns' ) );
		add_filter( 'manage_edit-form_request_sortable_columns', array( $this, 'edit_grid_sortable_columns' ) );
		add_action( 'manage_form_request_posts_custom_column', array( $this, 'print_grid_data' ) );

		add_action( 'restrict_manage_posts', array( $this, 'add_grid_filter_controls' ) );
		add_filter( 'parse_query', array( $this, 'apply_filter_query' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10, 1 );
	}

	/**
	 * Check is Contact Form 7 plugin is activated
	 *
	 * @return bool
	 */
	public static function check_requirements() {
		return is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );
	}

	/**
	 * Registration function
	 */
	public function init() {
		$this->label_singular = 'Request';
		$this->label_multiple = 'Requests';
		$this->textdomain     = '_jmvt';

		$this->has_single       = false;
		$this->is_searchable    = false;
		$this->rewrite_singular = home_url();

		$this->is_hierarchical = false;

		$this->has_admin_menu = 'wpcf7';

		$this->supports = array(
			self::SUPPORTS_TITLE,
		);

		$this->register();
	}

	/**
	 * Save form request into new post
	 *
	 * @return int|\WP_Error
	 */
	public function save_form_request() {

		$wpcf7        = \WPCF7_ContactForm::get_current();
		$form_request = $_POST;

		foreach ( $form_request as $key => $val ) {
			if ( in_array( $key, $this->system_fields, true ) ) {
				unset( $form_request[ $key ] );
			}
		}
		$form_request['_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

		$post_content = implode( "\n", $form_request );
		$post_title   = substr( strip_tags( implode( ' * ', $form_request ) ), 0, 64 ) . '...';

		$args = array(
			'post_title'   => $post_title,
			'post_status'  => self::STATUS_PENDING,
			'post_content' => $post_content,
			'post_parent'  => $wpcf7->id(),
			'post_type'    => self::$ID,
		);

		$post_id = wp_insert_post( $args );

		$form_request = base64_encode( wp_json_encode( $form_request ) );
		add_post_meta( $post_id, 'form_request', ( $form_request ) );
		add_post_meta( $post_id, 'remote_addr', $_SERVER['REMOTE_ADDR'] );
		add_post_meta( $post_id, 'request_uri', $_SERVER['REQUEST_URI'] );

		return $post_id;
	}

	/**
	 * Create new columns in posts grid page
	 *
	 * @param array $columns Columns available in posts list.
	 *
	 * @return mixed
	 */
	public function edit_grid_columns( $columns ) {
		unset( $columns['date'] );
		$columns['form_title']  = __( 'Form', $this->textdomain );
		$columns['remote_addr'] = __( 'IP Address', $this->textdomain );
		$columns['request_uri'] = __( 'Request URI', $this->textdomain );
		$columns['date']        = __( 'Date', $this->textdomain );

		return $columns;
	}

	/**
	 * Create new sortable columns in posts grid page
	 *
	 * @param array $columns Columns available in posts list.
	 *
	 * @return mixed
	 */
	public function edit_grid_sortable_columns( $columns ) {
		$columns['form_title'] = 'form_title';

		return $columns;
	}

	/**
	 * Insert data into new columns
	 *
	 * @param string $column Column to print data for.
	 */
	public function print_grid_data( $column ) {
		global $post;

		switch ( $column ) {
			case 'form_title':
				$form_id    = wp_get_post_parent_id( $post->ID );
				$form_title = get_the_title( $form_id );
				echo wp_kses( $form_title, array() );
				break;
			case 'remote_addr':
				echo esc_html( get_post_meta( $post->ID, 'remote_addr', true ) );
				break;
			case 'request_uri':
				echo esc_html( get_post_meta( $post->ID, 'request_uri', true ) );
				break;
		}
	}

	/**
	 * Print posts filter structure
	 */
	public function add_grid_filter_controls() {
		global $typenow;
		if ( $typenow !== self::$ID ) {
			return;
		}

		$forms_list = get_posts( array(
			'post_type' => 'wpcf7_contact_form',
		) );

		$select       = '<select name="parent_form">';
		$select      .= '<option value="">All Forms</option>';
		$current_form = ! empty( $_GET['parent_form'] ) ? $_GET['parent_form'] : '';
		foreach ( $forms_list as $form ) {
			$select .= '<option value="' . $form->ID . '" '
				. selected( $form->ID, $current_form, false )
				. '>' . esc_html( $form->post_title ) . '</option>';
		}
		$select .= '</select>';

		print $select;
	}

	/**
	 * Create query in posts filter
	 *
	 * @param \WP_Query $query Current query object.
	 *
	 * @return bool
	 */
	public function apply_filter_query( $query ) {
		global $pagenow;
		if ( 'edit.php' !== $pagenow || self::$ID !== $query->query_vars['post_type'] || empty( $_GET['parent_form'] ) ) {
			return false;
		}

		$query->query_vars['post_parent'] = $_GET['parent_form'];

		return true;
	}

	/**
	 * Add new custom metabox in single post
	 */
	public function add_metabox() {
		add_meta_box( 'form_request_info', __( 'Request Info', $this->textdomain ), array(
			$this,
			'render_form_request_fields',
		), self::$ID, 'normal', 'default' );
	}

	/**
	 * Render form request in metabox
	 */
	public function render_form_request_fields() {
		global $post;

		$form_request = get_post_meta( $post->ID, 'form_request', true );
		$form_request = json_decode( base64_decode( $form_request ), true );
		if ( empty( $form_request ) ) {
			esc_html_e( 'Unable to decode the response.', $this->textdomain );
		}

		$output  = '';
		$output .= '<table class="form-table">';

		foreach ( $form_request as $key => $field ) {
			if ( in_array( $key, $this->system_fields, true ) ) {
				continue;
			}

			$key     = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
			$output .= '<tr>';
			$output .= $this->print_field_label( $key );
			if ( is_array( $field ) ) {
				$output .= '<td><span>';
				foreach ( $field as $value ) {
					$output .= esc_html( $value ) . '<br />';
				}
				$output .= '</span></td>';
			} else {
				$output .= '<td><pre>' . esc_html( $field ) . '</pre></td>';
			}
			$output .= '</tr>';
		}

		$remote_addr = get_post_meta( $post->ID, 'remote_addr', true );
		$request_uri = get_post_meta( $post->ID, 'request_uri', true );

		$output .= '<tfoot>
			<tr>' . $this->print_field_label( 'IP Address' ) . '<td><span>' . esc_html( $remote_addr ) . '</span></td></tr> 
			<tr>' . $this->print_field_label( 'Request Uri' ) . '<td><span>' . esc_html( $request_uri ) . '</span></td></tr> 
		</tfoot>';

		$output .= '</table>';

		// few adjustments to post UI, unable to edit title and make Publish button read "Mark as read".
		$output .= "<script>
			jQuery('#save-post').hide();
			jQuery('#publish').attr('value', 'Mark as read');
			jQuery('#title').attr('readonly', true);
		</script>";

		print $output;
	}

	/**
	 * Prepare HTML for field label
	 *
	 * @param string $key Field key.
	 *
	 * @return string
	 */
	protected function print_field_label( $key ) {
		$key = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );

		return '<td width="300"><h3 style="margin: 0;">' . esc_html( $key ) . '</h3></td>';
	}
}
