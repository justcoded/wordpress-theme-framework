<?php
namespace JustCoded\ThemeFramework\SOPanels;

/**
 * Class SiteOriginPanelsLoader
 * SiteOrigin Panels Page Builder plugin extension
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class SiteOriginPanelsLoader {
	/**
	 * Widgets class names to be disabled from SiteOrigin Widgets Bundle
	 *
	 * @var array
	 */
	public $disabled_siteorigin_widgets = array(
		'SiteOrigin_Panels_Widgets_PostContent',
		'SiteOrigin_Widget_Button_Widget',
		'SiteOrigin_Widget_SocialMediaButtons_Widget',
		'SiteOrigin_Widget_Slider_Widget',
		'SiteOrigin_Widget_PostCarousel_Widget',
		'SiteOrigin_Widget_Features_Widget',
		'SiteOrigin_Widget_Headline_Widget',
		'SiteOrigin_Widget_Hero_Widget',
		'SiteOrigin_Widget_Cta_Widget',
		'SiteOrigin_Widgets_ContactForm_Widget',
		'SiteOrigin_Widgets_ImageGrid_Widget',
		'SiteOrigin_Widgets_Testimonials_Widget',
		'SiteOrigin_Widget_Simple_Masonry_Widget',
		'SiteOrigin_Widget_PriceTable_Widget',
	);

	/**
	 * Widgets class names to be disabled from standard Wordpress installation
	 *
	 * @var array
	 */
	public $disabled_wordpress_widgets = array(
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
	);

	/**
	 * Default RowLayout to be loaded if it's not specified by Row settings manually
	 *
	 * @var string
	 */
	public $default_row_layout = '\JustCoded\ThemeFramework\SOPanels\Grid12RowLayout';

	/**
	 * Default namespace of rows/widgets to be loaded
	 *
	 * @var string
	 */
	public $default_layout_namespace = '\JustCoded\ThemeFramework\SOPanels';

	/**
	 * Registered row layouts
	 *
	 * @var array
	 */
	protected $layouts = array();

	/**
	 * Registered widgets layouts
	 *
	 * @var array
	 */
	protected $widgets = array();

	/**
	 * Current row index to be rendered
	 *
	 * @var int
	 */
	private $_row_index = - 1;

	/**
	 * Current column index to be rendered
	 *
	 * @var int
	 */
	private $_col_index = - 1;

	/**
	 * General cell index (multi-row value)
	 *
	 * @var int
	 */
	private $_cell_index = - 1;

	/**
	 * Widget index to be rendered
	 *
	 * @var int
	 */
	private $_widget_index = - 1;

	/**
	 * Widget styles settings cache variable
	 *
	 * @var null
	 */
	private $_widget_styles = null;

	/**
	 * Main class constructor
	 * Set WordPress actions and filters
	 */
	public function __construct() {
		// custom row options hook.
		add_filter( 'siteorigin_panels_row_style_fields', array( $this, 'update_row_style_fields' ) );
		// custom styles hooks.
		add_filter( 'siteorigin_panels_layout_attributes', array( $this, 'set_panels_attributes' ), 10, 3 );
		add_filter( 'siteorigin_panels_row_attributes', array( $this, 'set_row_wrapper_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_style_attributes', array( $this, 'set_row_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_cell_attributes', array( $this, 'set_cell_wrapper_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_cell_style_attributes', array( $this, 'set_cell_attributes' ), 10, 2 );

		add_filter( 'siteorigin_panels_widget_style_fields', array( $this, 'update_widget_style_fields' ), 10, 2 );
		add_filter( 'siteorigin_panels_widget_style_attributes', array( $this, 'set_widget_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_widget_classes', array( $this, 'set_widget_wrapper_classes' ), 10, 3 );

		add_filter( 'siteorigin_panels_before_row', array( $this, 'set_row_before' ), 10, 3 );
		add_filter( 'siteorigin_panels_after_row', array( $this, 'set_row_after' ), 10, 3 );

		// more classes clean up.
		add_filter( 'siteorigin_panels_row_style_classes', array( $this, 'cleanup_panel_style_classes' ) );
		add_filter( 'siteorigin_panels_cell_style_classes', array( $this, 'cleanup_panel_style_classes' ) );
		add_filter( 'siteorigin_panels_widget_style_classes', array( $this, 'cleanup_panel_style_classes' ) );

		// detach inline css.
		add_action( 'init', array( $this, 'unregister_siteorigin_styles' ), 1000 );

		// detach standard SO widgets, which are not used.
		add_action( 'widgets_init', array( $this, 'unregister_siteorigin_widgets' ), 1000 );
		add_filter( 'siteorigin_panels_widgets', array( $this, 'hide_siteorigin_required_widgets' ) );

		// disable prebuilt layouts.
		add_filter( 'init', array( $this, 'hide_siteorigin_prebuilt_layouts' ), 1000 );

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
		if ( ! empty( $this->default_row_layout ) ) {
			$this->register_row_layout( $this->default_row_layout, 'Default' );
		}
	}

	/**
	 * Register widget layouts available
	 *
	 * @param string $class_name relative or absolute class name of Widget Layout to be registered.
	 */
	public function register_widget_layout( $class_name ) {
		if ( strpos( $class_name, '\\' ) !== 0 ) {
			$class_name = $this->default_layout_namespace . '\\' . $class_name;
		}

		$widget_layout                        = new $class_name;
		$this->widgets[ $widget_layout::$ID ] = $widget_layout;
	}

	/**
	 * Modify Widget object fields, which are shown at the right of widget editing
	 *
	 * @param array $fields standard fields options.
	 *
	 * @return array modified fields options.
	 */
	public function update_widget_style_fields( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'widget_css',
			'class',
			// layout.
			'bottom_margin',
			'gutter',
			'padding',
			'row_stretch',
			'font_color',
			'link_color',
			'mobile_padding',
			'collapse_order',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		// register custom Layout selector.
		$widgets                   = $this->get_list_widgets();
		$fields['widget_template'] = array(
			'name'     => 'Widget layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $widgets,
			'priority' => 10,
		);

		return $fields;
	}

	/**
	 * Check if current widget has some specific layout
	 * Return Layout class object if in use
	 *
	 * @param array $style_data  style data from siteorigin hook.
	 *
	 * @return SiteOriginWidget|null
	 */
	protected function check_widget_layout_in_use( $style_data ) {
		if ( ! empty( $style_data['widget_template'] ) ) {
			$layout_key = $style_data['widget_template'];
			if ( isset( $this->widgets[ $layout_key ] ) ) {
				return $this->widgets[ $layout_key ];
			}
		}

		return null;
	}

	/**
	 * Modify Widget attributes on frontend (render process)
	 * This method called before wrapper classes filter, that's why we init widget styles here to use in classes filter
	 *
	 * @param array $attributes   widget container attributes.
	 * @param array $style_data   widget style settings.
	 *
	 * @return array
	 */
	public function set_widget_attributes( $attributes, $style_data ) {
		// clean up all. this disable the div at all.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		if ( isset( $attributes['class'] ) ) {
			unset( $attributes['class'] );
		}

		$this->_widget_index ++;
		$this->_widget_styles = $style_data;

		if ( $layout = $this->check_widget_layout_in_use( $style_data ) ) {
			$layout->widget_index = $this->_widget_index;
			$attributes           = $layout->widget( $attributes, $style_data );
		}

		return $attributes;
	}

	/**
	 * Modify Widget wrapper classes (we can't modify any other attributes here)
	 *
	 * @param array  $classes html attribute.
	 * @param string $widget widget class.
	 * @param array  $instance widgets data.
	 *
	 * @return array
	 */
	public function set_widget_wrapper_classes( $classes, $widget, $instance ) {
		$classes = array(
			'jpnl-widget-wrapper',
			'jpnl-wi-' . $this->_widget_index,
		);
		if ( $layout = $this->check_widget_layout_in_use( $this->_widget_styles ) ) {
			$classes = $layout->wrapper_classes( $classes, $this->_widget_styles, $widget, $instance );
		}
		$this->_widget_styles = null;

		return $classes;
	}

	/**
	 * Register row layouts available
	 *
	 * @param string $class_name relative or absolute class name of Row Layout to be registered.
	 * @param string $title optional. to rewrite row layout title.
	 */
	public function register_row_layout( $class_name, $title = '' ) {
		if ( strpos( $class_name, '\\' ) !== 0 ) {
			$class_name = $this->default_layout_namespace . '\\' . $class_name;
		}

		$layout = new $class_name;

		if ( ! empty( $title ) ) {
			$layout::$TITLE = $title;
		}
		$this->layouts[ $layout::$ID ] = $layout;
	}

	/**
	 * Adjust Page Builder Layout group options.
	 * Remove standard fields for margin/gutter/padding/strech.
	 * Add custom Row Layout which will affect row/cell classes
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	public function update_row_style_fields( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'class',
			'cell_class',
			'row_css',
			'id',
			// layout.
			'bottom_margin',
			'gutter',
			'padding',
			'row_stretch',
			'mobile_padding',
			'collapse_order',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		// register custom Layout selector.
		$layouts                = $this->get_list_layouts();
		$fields['row_template'] = array(
			'name'     => 'Row layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $layouts,
			'priority' => 10,
		);

		return $fields;
	}

	/**
	 * Return list of available layouts
	 *
	 * @return array   (id, title) pairs
	 */
	protected function get_list_layouts() {
		$list = array();

		if ( empty( $this->default_row_layout ) ) {
			$list[''] = 'Default';
		}
		foreach ( $this->layouts as $key => $lt ) {
			$list[ $key ] = $lt::$TITLE;
		}

		return $list;
	}

	/**
	 * Return list of available layouts
	 *
	 * @return array   (id, title) pairs
	 */
	protected function get_list_widgets() {
		$list = array(
			'' => 'Default',
		);
		foreach ( $this->widgets as $key => $lt ) {
			$list[ $key ] = $lt::$TITLE;
		}

		return $list;
	}

	/**
	 * Remove hard-coded classes which are not controlled with wrapper attributes filter
	 *
	 * @param array $classes  container html classes.
	 *
	 * @return array
	 */
	public function cleanup_panel_style_classes( $classes ) {
		$cleanup = array(
			'panel-row-style',
			'panel-cell-style',
			'panel-widget-style',
		);
		foreach ( $cleanup as $needle ) {
			$key = array_search( $needle, $classes, true );
			if ( false !== $key ) {
				unset( $classes[ $key ] );
			}
		}

		return $classes;
	}

	/**
	 * Check if current row has some specific layout
	 * Return Layout class object if in use
	 *
	 * @param array $style_data  style settings.
	 *
	 * @return SiteOriginLayout|null
	 */
	protected function check_layout_in_use( $style_data ) {
		if ( isset( $style_data['style'] ) ) {
			$style_data = $style_data['style'];
		} elseif ( isset( $style_data['grids'][ $this->_row_index ]['style'] ) ) {
			$style_data = $style_data['grids'][ $this->_row_index ]['style'];
		}

		if ( ! empty( $style_data['row_template'] ) ) {
			$layout_key = $style_data['row_template'];
			if ( isset( $this->layouts[ $layout_key ] ) ) {
				return $this->layouts[ $layout_key ];
			}
		} elseif ( ! empty( $this->default_row_layout ) ) {
			return reset( $this->layouts );
		}

		return null;
	}

	/**
	 * Main wrapper of the page builder content
	 *
	 * @param array   $attributes  panel container attributes.
	 * @param integer $post_id     post ID.
	 * @param array   $panels_data Panels data.
	 *
	 * @return array
	 */
	public function set_panels_attributes( $attributes, $post_id, $panels_data ) {
		// kill ID attribute.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}

		$attributes['class'] = 'jpnl-content';

		return $attributes;
	}

	/**
	 * Row wrapper hook callback
	 *
	 * @param array $attributes  row wrapper div attributes.
	 * @param array $panel_data  panel data array.
	 *
	 * @return mixed
	 */
	public function set_row_wrapper_attributes( $attributes, $panel_data ) {
		$this->_row_index ++;
		$this->_col_index = - 1;

		// remove siteorigin id, class and apply our default.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = 'jpnl-row-wrap jpnl-cols-' . $panel_data['cells'];

		if ( $layout = $this->check_layout_in_use( $panel_data ) ) {
			$layout->row_index = $this->_row_index;
			$layout->col_index = $this->_col_index;
			$attributes        = $layout->row_wrapper( $attributes, $panel_data );
		}

		return $attributes;
	}

	/**
	 * Row hook callback
	 *
	 * @param array $attributes  row div attributes.
	 * @param array $style_data  row style settings array.
	 *
	 * @return mixed
	 */
	public function set_row_attributes( $attributes, $style_data ) {
		// remove siteorigin id, class and apply our default.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = array( 'jpnl-row' );

		if ( $layout = $this->check_layout_in_use( $style_data ) ) {
			$attributes = $layout->row( $attributes, $style_data );
		}

		return $attributes;
	}

	/**
	 * Cell wrapper hook callback
	 *
	 * @param array $attributes  row wrapper div attributes.
	 * @param array $panel_data  panel data array.
	 *
	 * @return mixed
	 */
	public function set_cell_wrapper_attributes( $attributes, $panel_data ) {
		$this->_col_index ++;
		$this->_cell_index ++;
		$this->_widget_index = - 1;

		// remove siteorigin id, class and apply our default.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = 'jpnl-cell-wrap jpnl-col-index-' . $this->_col_index;

		if ( $layout = $this->check_layout_in_use( $panel_data ) ) {

			$layout->col_index  = $this->_col_index;
			$layout->cell_index = $this->_cell_index;
			$attributes         = $layout->cell_wrapper( $attributes, $panel_data );
		}

		return $attributes;
	}

	/**
	 * Cell hook callback
	 *
	 * @param array $attributes  row div attributes.
	 * @param array $style_data  row style settings array.
	 *
	 * @return mixed
	 */
	public function set_cell_attributes( $attributes, $style_data ) {
		// remove siteorigin id, class and apply our default.
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = array( 'jpnl-cell' );

		if ( $layout = $this->check_layout_in_use( $style_data ) ) {
			$attributes = $layout->cell( $attributes, $style_data );
		}

		return $attributes;
	}

	/**
	 * Row Before callback
	 *
	 * @param string $html  Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data  grid data options.
	 *
	 * @return mixed
	 */
	public function set_row_before( $html, $panel_data, $grid_data ) {
		if ( $layout = $this->check_layout_in_use( $panel_data ) ) {
			$html = $layout->before_row( $html, $panel_data, $grid_data );
		}

		return $html;
	}

	/**
	 * Row After callback
	 *
	 * @param string $html  Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data  grid data options.
	 *
	 * @return mixed
	 */
	public function set_row_after( $html, $panel_data, $grid_data ) {
		if ( $layout = $this->check_layout_in_use( $panel_data ) ) {
			$html = $layout->after_row( $html, $panel_data, $grid_data );
		}

		return $html;
	}

	/**
	 * Unset loading inline styles generated for the page builder layout
	 */
	public function unregister_siteorigin_styles() {
		wp_dequeue_style( 'siteorigin-panels-front' );
		remove_action( 'wp_head', 'siteorigin_panels_print_inline_css', 12 );
		remove_action( 'wp_footer', 'siteorigin_panels_print_inline_css' );
	}

	/**
	 * Unregister widgets which are not very good and hard to use
	 *
	 * @global \WP_Widget_Factory $wp_widget_factory
	 */
	public function unregister_siteorigin_widgets() {
		global $wp_widget_factory;

		foreach ( $this->disabled_siteorigin_widgets as $widget_class ) {
			if ( isset( $wp_widget_factory->widgets[ $widget_class ] ) ) {
				unregister_widget( $widget_class );
			}
		}
	}

	/**
	 * Unregister ajax hooks which allows to enable prebuilt layouts and show layouts directory
	 * (User can choose some layout, which do not work actually)
	 */
	public function hide_siteorigin_prebuilt_layouts() {
		remove_action( 'wp_ajax_so_panels_directory_enable', 'siteorigin_panels_ajax_directory_enable' );
		remove_action( 'wp_ajax_so_panels_directory_query', 'siteorigin_panels_ajax_directory_query' );
	}

	/**
	 * Remove hard-coded widgets from the "Add Widget" page builder popup
	 *
	 * @param array $widgets  widgets available for page builder.
	 *
	 * @return array
	 */
	public function hide_siteorigin_required_widgets( $widgets ) {
		// unset WordPress widgets from allowed widgets for page builder.
		foreach ( $this->disabled_wordpress_widgets as $widget_class ) {
			if ( isset( $widgets[ $widget_class ] ) ) {
				unset( $widgets[ $widget_class ] );
			}
		}

		// unset Site Origin widgets from allowed widgets for page builder.
		foreach ( $this->disabled_siteorigin_widgets as $widget_class ) {
			if ( isset( $widgets[ $widget_class ] ) ) {
				unset( $widgets[ $widget_class ] );
			}
		}

		return $widgets;
	}
}
