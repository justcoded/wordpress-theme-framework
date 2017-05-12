<?php
namespace JustCoded\ThemeFramework\PageBuilder\v25;

use JustCoded\ThemeFramework\PageBuilder\v25\Traits\HtmlCleanup;
use JustCoded\ThemeFramework\PageBuilder\v25\Traits\RowLayoutsLoader;
use JustCoded\ThemeFramework\PageBuilder\v25\Traits\WidgetLayoutsLoader;

/**
 * Class SiteOriginPanelsLoader
 * SiteOrigin Panels Page Builder plugin extension
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class PageBuilderLoader {
	use HtmlCleanup, RowLayoutsLoader, WidgetLayoutsLoader;

	/**
	 * Widgets class names to be disabled from SiteOrigin Widgets Bundle
	 *
	 * @var array
	 */
	public $disabled_siteorigin_widgets = array(
		'editor' => 'SiteOrigin_Widget_Editor_Widget',
		'button' => 'SiteOrigin_Widget_Button_Widget',
		'image' => 'SiteOrigin_Widget_Image_Widget',
		'slider' => 'SiteOrigin_Widget_Slider_Widget',
		'features' => 'SiteOrigin_Widget_Features_Widget',
		'post-carousel' => 'SiteOrigin_Widget_PostCarousel_Widget',
	);

	/**
	 * Widgets class names to be disabled from standard Wordpress installation
	 *
	 * @var array
	 */
	public $disabled_widgets = array(
		'WP_Widget_Pages',
		'WP_Widget_Calendar',
		'WP_Widget_Archives',
		'WP_Widget_Meta',
		'WP_Widget_Categories',
		'WP_Widget_RSS',
		'WP_Widget_Search',
		'WP_Widget_Recent_Posts',
		'WP_Widget_Recent_Comments',
		'WP_Widget_Tag_Cloud',
		'WP_Nav_Menu_Widget',

		'SiteOrigin_Panels_Widgets_PostContent',
	);

	/**
	 * Default namespace of rows/widgets to be loaded
	 *
	 * @var string
	 */
	public $default_layout_namespace = '\JustCoded\ThemeFramework\PageBuilder\v25';

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
	 * Main class constructor
	 * Set WordPress actions and filters
	 */
	public function __construct() {
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
	 * @param array $widgets  widgets available for page builder.
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

		return $widgets;
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

}
