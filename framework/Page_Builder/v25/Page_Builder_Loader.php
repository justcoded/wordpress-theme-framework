<?php

namespace JustCoded\WP\Framework\Page_Builder\v25;

use JustCoded\WP\Framework\Objects\Singleton;
use JustCoded\WP\Framework\Page_Builder\v25\Traits\Html_Cleanup;
use JustCoded\WP\Framework\Page_Builder\v25\Traits\Row_Layouts_Loader;
use JustCoded\WP\Framework\Page_Builder\v25\Traits\Widget_Layouts_Loader;
use JustCoded\WP\Framework\Page_Builder\v25\Traits\Widget_Fields_Loader;

/**
 * Class SiteOriginPanelsLoader
 * SiteOrigin Panels Page Builder plugin extension
 *
 * @package JustCoded\WP\Framework\SOPanels
 */
class Page_Builder_Loader {
	use Singleton;
	use Html_Cleanup, Row_Layouts_Loader, Widget_Layouts_Loader, Widget_Fields_Loader;

	/**
	 * Widgets class names to be disabled from SiteOrigin Widgets Bundle
	 *
	 * @var array
	 */
	public $disabled_siteorigin_widgets = array(
		'editor'        => 'SiteOrigin_Widget_Editor_Widget',
		'button'        => 'SiteOrigin_Widget_Button_Widget',
		'image'         => 'SiteOrigin_Widget_Image_Widget',
		'slider'        => 'SiteOrigin_Widget_Slider_Widget',
		'features'      => 'SiteOrigin_Widget_Features_Widget',
		'post-carousel' => 'SiteOrigin_Widget_PostCarousel_Widget',
		'post-loop'     => 'SiteOrigin_Panels_Widgets_Layout',
		'page-builder'  => 'SiteOrigin_Panels_Widgets_PostLoop',
		'post-content'  => 'SiteOrigin_Panels_Widgets_PostContent',
	);

	/**
	 * Widgets class names to be disabled from standard WordPress installation
	 *
	 * @var array
	 */
	public $disabled_widgets = array(
		'WP_Widget_Pages',
		'WP_Widget_Calendar',
		'WP_Widget_Archives',
		'WP_Widget_Media_Image',
		'WP_Widget_Meta',
		'WP_Widget_Categories',
		'WP_Widget_RSS',
		'WP_Widget_Search',
		'WP_Widget_Recent_Posts',
		'WP_Widget_Recent_Comments',
		'WP_Widget_Tag_Cloud',
		'WP_Nav_Menu_Widget',
		'WP_Widget_Custom_HTML',
		'WP_Widget_Media_Audio',
		'WP_Widget_Media_Video',
		'WP_Widget_Media_Gallery',

		'SiteOrigin_Panels_Widgets_PostContent',
	);

	/**
	 * Default namespace of rows/widgets to be loaded
	 *
	 * @var string
	 */
	public $default_layout_namespace = '\JustCoded\WP\Framework\Page_Builder\v25';

	/**
	 * Current row index to be rendered
	 *
	 * @var int
	 */
	protected $_row_index = - 1;

	/**
	 * Current column index to be rendered
	 *
	 * @var int
	 */
	protected $_col_index = - 1;

	/**
	 * General cell index (multi-row value)
	 *
	 * @var int
	 */
	protected $_cell_index = - 1;

	/**
	 * Widget index to be rendered
	 *
	 * @var int
	 */
	protected $_widget_index = - 1;

	/**
	 * Whether we need elements to be indexed.
	 *
	 * @var int
	 */
	protected $index_elements = false;

	/**
	 * Main class constructor
	 * Set WordPress actions and filters
	 */
	protected function __construct() {
		$this->fields_loader();
		$this->html_cleanup();
		$this->row_layouts_loader();
		$this->widget_layouts_loader();

		// custom row options hook.
		add_filter( 'siteorigin_panels_row_style_fields', array( $this, 'remove_so_row_options' ) );
		add_filter( 'siteorigin_panels_widget_style_fields', array( $this, 'remove_so_widget_options' ), 10, 2 );

		// hide unnecessary widgets.
		add_filter( 'siteorigin_panels_widgets', array( $this, 'hide_disabled_widgets' ) );

		// disable layouts directory: feature to import some layout from web (it won't work in our theme).
		add_filter( 'siteorigin_panels_layouts_directory_enabled', array( $this, 'disabled_layouts_directory' ) );

		// add own hook for custom widgets preview.
		add_action( 'wp_ajax_so_widgets_preview', array( $this, 'widget_preview' ), 5 );

		// filter widget dialog tabs.
		add_filter( 'siteorigin_panels_widget_dialog_tabs', array( $this, 'update_widgets_dialog_tabs' ), 20 );

		$this->init();
	}

	/**
	 * Check that required plugin is installed and activated
	 *
	 * @return bool
	 */
	public static function plugin_active() {
		return is_plugin_active( 'siteorigin-panels/siteorigin-panels.php' );
	}

	/**
	 * Check that widgets bundle plugin is installed and activated
	 *
	 * @return bool
	 */
	public static function widgets_bundle_active() {
		return is_plugin_active( 'so-widgets-bundle/so-widgets-bundle.php' );
	}

	/**
	 * Init row/widgets layouts and change disabled plugins list.
	 * Called at the end of the __contruct() method
	 *
	 * To add row layout please call:
	 * $this->register_row_layout( 'LayoutClassName' );
	 *        OR
	 * $this->register_widget_layout( 'LayoutClassName' );
	 *
	 *
	 * To enable back some plugins please do the following:
	 * unset(array_search('Widget_Class_name', $this->disabledWordPressWidgets));
	 *        OR
	 * unset(array_search('Widget_Class_name', $this->disabledSiteOriginWidgets));
	 */
	public function init() {
	}

	/**
	 * Adjust Page Builder Layout group options.
	 * Remove standard fields for margin/gutter/padding/strech.
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	public function remove_so_row_options( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'class',
			'cell_class',
			'row_css',
			'id',
			'mobile_css',
			// layout.
			'bottom_margin',
			'gutter',
			'padding',
			'row_stretch',
			'mobile_padding',
			'collapse_order',
			'collapse_behaviour',
			'cell_alignment',
			'border_color',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		return $fields;
	}

	/**
	 * Modify Widget object fields, which are shown at the right of widget editing
	 *
	 * @param array $fields standard fields options.
	 *
	 * @return array modified fields options.
	 */
	public function remove_so_widget_options( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'id',
			'class',
			'widget_css',
			'mobile_css',
			// layout.
			'padding',
			'mobile_padding',
			'margin',
			// design.
			'font_color',
			'link_color',
			'border_color',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		return $fields;
	}

	/**
	 * Remove hard-coded widgets from the "Add Widget" page builder popup
	 *
	 * @param array $widgets widgets available for page builder.
	 *
	 * @return array
	 */
	public function hide_disabled_widgets( $widgets ) {
		// unset WordPress widgets from allowed widgets for page builder.
		foreach ( $this->disabled_widgets as $widget_class ) {
			if ( isset( $widgets[ $widget_class ] ) ) {
				unset( $widgets[ $widget_class ] );
			}
		}

		if ( ! is_plugin_active( 'so-widgets-bundle/so-widgets-bundle.php' ) ) {
			// unset Site Origin widgets from allowed widgets for page builder.
			foreach ( $this->disabled_siteorigin_widgets as $widget_class ) {
				if ( isset( $widgets[ $widget_class ] ) ) {
					unset( $widgets[ $widget_class ] );
				}
			}
		} else {
			// hide disabled widgets.
			$active_widgets = get_option( 'siteorigin_widgets_active', array() );
			foreach ( $this->disabled_siteorigin_widgets as $key => $widget_class ) {
				if ( isset( $widgets[ $widget_class ] ) && empty( $active_widgets[ $key ] ) ) {
					unset( $widgets[ $widget_class ] );
				}
			}
		}

		$widgets['SiteOrigin_Widget_Editor_Widget']['groups'] = array( 'theme' );

		return $widgets;
	}

	/**
	 * Update widgets dialog tabs list, add new one
	 *
	 * @param array $tabs Widgets panel tabs list.
	 *
	 * @return array
	 */
	public function update_widgets_dialog_tabs( $tabs ) {
		$tabs[0]['message']              = '';
		$tabs['page_builder']['message'] = '';

		$sorted_tabs[0]             = $tabs[0];
		$sorted_tabs['recommended'] = $tabs['recommended'];
		unset( $tabs[0] );
		unset( $tabs['recommended'] );
		unset( $tabs['widgets_bundle'] );
		$sorted_tabs = array_merge( $sorted_tabs, $tabs );

		return $sorted_tabs;
	}

	/**
	 * Disabled layouts directory (import some external layouts)
	 *
	 * @param bool $enabled Is layouts directory enabled.
	 *
	 * @return bool
	 */
	public function disabled_layouts_directory( $enabled ) {
		return false;
	}

	/**
	 * Custom ajax action for custom widgets preview.
	 */
	public function widget_preview() {
		if ( empty( $_POST['class'] ) ) {
			exit();
		}
		if ( empty( $_REQUEST['_widgets_nonce'] )
			|| ! wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' )
		) {
			return;
		}

		// we use namespaces, but widget factory miss first trailing slash. And WP pass double slashes in the middle.
		$class = '\\' . trim( str_replace( '\\\\', '\\', $_POST['class'] ), '\\' );

		// Get the widget from the widget factory.
		global $wp_widget_factory;
		$widget = ! empty( $wp_widget_factory->widgets[ $class ] ) ? $wp_widget_factory->widgets[ $class ] : false;

		if ( ! is_a( $widget, '\JustCoded\WP\Framework\Page_Builder\v25\Page_Builder_Widget' ) ) {
			return;
		}

		$instance = json_decode( stripslashes_deep( $_POST['data'] ), true );
		/* @var \SiteOrigin_Widget $widget */
		$instance               = $widget->update( $instance, $instance );
		$instance['is_preview'] = true;

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'so-widget-preview', plugin_dir_url( SOW_BUNDLE_BASE_FILE ) . 'base/css/preview.css', array(), rand( 0, 65536 ) );
		wp_enqueue_style( 'jtf-widget-preview', plugin_dir_url( JTF_PLUGIN_FILE ) . 'assets/css/widget-preview.css', array(), rand( 0, 65536 ) );

		wp_enqueue_script( 'jtc-widget-preview', plugin_dir_url( JTF_PLUGIN_FILE ) . 'assets/js/widget-preview.js', array( 'jquery' ) );
		$sowb = \SiteOrigin_Widgets_Bundle::single();
		$sowb->register_general_scripts();

		ob_start();
		$widget->preview( array(
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		), $instance );
		$widget_preview = ob_get_clean();

		?>
		<html>
		<head>
			<title><?php _e( 'Widget Example' ); ?></title>
			<?php
			wp_print_scripts();
			wp_print_styles();
			siteorigin_widget_print_styles();
			?>
		</head>
		<body>
		<?php // A lot of themes use entry-content as their main content wrapper. ?>
		<div class="entry-content">
			<div class="widget-preview">
				<?php echo $widget_preview; ?>
			</div>
		</div>
		</body>
		</html>

		<?php
		exit();
	}
}
