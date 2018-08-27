<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Traits;

use JustCoded\WP\Framework\Page_Builder\v25\Layouts\Row_Layout;

trait Row_Layouts_Loader {
	/**
	 * Registered row layouts
	 *
	 * @var Row_Layout[]
	 */
	protected $layouts = array();

	/**
	 * Define is layout currently in use for specific row.
	 *
	 * @var null|Row_Layout
	 */
	protected $_row_layout = null;

	/**
	 * Row_Layouts_Loader constructor
	 * (have to be called inside class constructor)
	 */
	public function row_layouts_loader() {
		// custom row layout option.
		add_filter( 'siteorigin_panels_row_style_fields', array( $this, 'add_row_options' ) );

		// custom styles hooks.
		add_filter( 'siteorigin_panels_row_attributes', array( $this, 'set_row_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_style_attributes', array( $this, 'set_row_inner_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_cell_attributes', array( $this, 'set_cell_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_cell_style_attributes', array( $this, 'set_cell_inner_attributes' ), 10, 2 );

		add_filter( 'siteorigin_panels_before_row', array( $this, 'set_row_before' ), 10, 3 );
		add_filter( 'siteorigin_panels_after_row', array( $this, 'set_row_after' ), 10, 3 );
	}

	/**
	 * Adds new layout option.
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	public function add_row_options( $fields ) {
		$layouts = $this->get_list_layouts();
		if ( empty( $layouts ) ) {
			unset( $fields['background'] );
			unset( $fields['background_image_attachment'] );
			unset( $fields['background_display'] );
			return $fields;
		}

		$fields['row_template'] = array(
			'name'     => 'Row layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $layouts,
			'priority' => 10,
		);

		// add additional options from layouts.
		foreach ( $this->layouts as $layout ) {
			if ( $options = $layout->options() ) {
				$fields = array_merge( $fields, $options );
			}
		}

		return $fields;
	}

	/**
	 * Register row layouts available
	 *
	 * @param string $class_name relative or absolute class name of Row Layout to be registered.
	 * @param string $title optional. to rewrite row layout title.
	 */
	public function register_row_layout( $class_name, $title = '' ) {
		if ( ! class_exists( $class_name ) && strpos( $class_name, '\\' ) !== 0 ) {
			$class_name = $this->default_layout_namespace . '\\' . $class_name;
		}

		$layout = new $class_name();

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
	 * @return Row_Layout|null
	 */
	protected function check_layout_in_use( $style_data ) {
		if ( ! empty( $style_data['row_template'] ) ) {
			$layout_key = $style_data['row_template'];
			if ( isset( $this->layouts[ $layout_key ] ) ) {
				return $this->layouts[ $layout_key ];
			}
		} elseif ( ! empty( $this->layouts ) ) {
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
	public function set_row_attributes( $attributes, $panel_data ) {
		if ( $this->_row_layout ) {
			$attributes = $this->_row_layout->row( $attributes, $panel_data );
		}

		return $attributes;
	}

	/**
	 * Row hook callback
	 * This one called before "row" because of SO logic.
	 *
	 * @param array $attributes  row div attributes.
	 * @param array $style_data  row style settings array.
	 *
	 * @return mixed
	 */
	public function set_row_inner_attributes( $attributes, $style_data ) {
		$this->_row_layout = $this->check_layout_in_use( $style_data );
		if ( $this->_row_layout ) {
			$this->_row_layout->row_index = $this->_row_index;
			$this->_row_layout->col_index = $this->_col_index;

			$attributes = $this->_row_layout->row_inner( $attributes, $style_data );
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
	public function set_cell_attributes( $attributes, $panel_data ) {
		if ( $this->_row_layout ) {
			$this->_row_layout->col_index  = $this->_col_index;
			$this->_row_layout->cell_index = $this->_cell_index;

			$attributes = $this->_row_layout->cell( $attributes, $panel_data );
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
	public function set_cell_inner_attributes( $attributes, $style_data ) {
		if ( $this->_row_layout ) {
			$attributes = $this->_row_layout->cell_inner( $attributes, $style_data );
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
		if ( $this->_row_layout ) {
			$html = $this->_row_layout->before_row( $html, $panel_data, $grid_data );
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
		if ( $this->_row_layout ) {
			$html = $this->_row_layout->after_row( $html, $panel_data, $grid_data );
		}

		return $html;
	}

}
