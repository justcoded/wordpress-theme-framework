<?php


namespace JustCoded\ThemeFramework\SOPanels\Traits;


trait RowLayoutsLoader {
	/**
	 * Adjust Page Builder Layout group options.
	 * Remove standard fields for margin/gutter/padding/strech.
	 * Add custom Row Layout which will affect row/cell classes
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	abstract public function update_row_style_fields( $fields );

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


}