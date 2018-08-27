<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Traits;

use JustCoded\WP\Framework\Page_Builder\v25\Layouts\Widget_Layout;

trait Widget_Layouts_Loader {

	/**
	 * Registered widgets layouts
	 *
	 * @var array
	 */
	protected $widgets = array();

	/**
	 * Current widget layout in use.
	 *
	 * @var null|Widget_Layout
	 */
	protected $_widget_layout = null;

	/**
	 * Widget styles settings cache variable
	 *
	 * @var null
	 */
	protected $_widget_styles = null;

	/**
	 * Widget_Layouts_Loader constructor
	 * (have to be called inside class constructor)
	 */
	public function widget_layouts_loader() {
		add_filter( 'siteorigin_panels_widget_style_fields', array( $this, 'add_widget_options' ), 10, 2 );
		add_filter( 'siteorigin_panels_widget_style_attributes', array( $this, 'set_widget_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_widget_classes', array( $this, 'set_widget_inner_classes' ), 10, 3 );
	}

	/**
	 * Adds new layout option.
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	public function add_widget_options( $fields ) {
		$layouts = $this->get_list_widgets();
		if ( empty( $layouts ) ) {
			unset( $fields['background'] );
			unset( $fields['background_image_attachment'] );
			unset( $fields['background_display'] );
			return $fields;
		}

		$fields['widget_template'] = array(
			'name'     => 'Widget layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $layouts,
			'priority' => 10,
		);

		// add additional options from layouts.
		foreach ( $this->widgets as $layout ) {
			if ( $options = $layout->options() ) {
				$fields = array_merge( $fields, $options );
			}
		}

		return $fields;
	}

	/**
	 * Register widget layouts available
	 *
	 * @param string $class_name relative or absolute class name of Widget Layout to be registered.
	 * @param string $title optional. to rewrite row layout title.
	 */
	public function register_widget_layout( $class_name, $title = '' ) {
		if ( ! class_exists( $class_name ) && strpos( $class_name, '\\' ) !== 0 ) {
			$class_name = $this->default_layout_namespace . '\\' . $class_name;
		}

		$widget_layout = new $class_name();
		if ( ! empty( $title ) ) {
			$widget_layout::$TITLE = $title;
		}

		$this->widgets[ $widget_layout::$ID ] = $widget_layout;
	}

	/**
	 * Return list of available layouts
	 *
	 * @return array   (id, title) pairs
	 */
	protected function get_list_widgets() {
		$list = array();
		foreach ( $this->widgets as $key => $lt ) {
			$list[ $key ] = $lt::$TITLE;
		}

		return $list;
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
		} elseif ( ! empty( $this->widgets ) ) {
			return reset( $this->widgets );
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
		$this->_widget_styles = $style_data;

		$this->_widget_layout = $this->check_widget_layout_in_use( $style_data );
		if ( $this->_widget_layout ) {
			$this->_widget_layout->widget_index = $this->_widget_index;
			$attributes                         = $this->_widget_layout->widget( $attributes, $style_data );
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
	public function set_widget_inner_classes( $classes, $widget, $instance ) {
		if ( $this->_widget_layout ) {
			$classes = $this->_widget_layout->widget_inner_classes( $classes, $this->_widget_styles, $widget, $instance );
		}
		$this->_widget_styles = null;

		return $classes;
	}
}
