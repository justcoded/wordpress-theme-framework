<?php


namespace JustCoded\ThemeFramework\SOPanels\Traits;


trait WidgetLayoutsLoader {
	/**
	 * Modify Widget object fields, which are shown at the right of widget editing
	 *
	 * @param array $fields standard fields options.
	 *
	 * @return array modified fields options.
	 */
	abstract public function update_widget_style_fields( $fields );

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
}