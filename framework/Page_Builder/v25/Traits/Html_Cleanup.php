<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Traits;

trait Html_Cleanup {

	/**
	 * Html_Cleanup constructor
	 * (have to be called inside class constructor)
	 */
	public function html_cleanup() {
		// detach inline css.
		add_action( 'init', array( $this, 'remove_siteorigin_inline_styles' ) );

		add_action( 'siteorigin_panels_data', array( $this, 'panels_data' ), 10, 2 );

		// html attributes filters.
		add_filter( 'siteorigin_panels_layout_attributes', array( $this, 'clean_panels_container_attributes' ), 10, 3 );
		add_filter( 'siteorigin_panels_row_attributes', array( $this, 'clean_row_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_style_attributes', array( $this, 'clean_row_inner_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_row_cell_attributes', array( $this, 'clean_cell_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_cell_style_attributes', array( $this, 'clean_cell_inner_attributes' ), 10, 2 );

		add_filter( 'siteorigin_panels_widget_attributes', array( $this, 'clean_widget_attributes' ), 10, 2 );
		add_filter( 'siteorigin_panels_widget_style_attributes', array( $this, 'clean_widget_inner_classes' ), 10, 2 );

		add_filter( 'siteorigin_panels_row_style_classes', array( $this, 'clean_css_classes' ) );
		add_filter( 'siteorigin_panels_cell_style_classes', array( $this, 'clean_css_classes' ) );
		add_filter( 'siteorigin_panels_widget_style_classes', array( $this, 'clean_css_classes' ) );
	}

	/**
	 * Unset loading inline styles generated for the page builder layout
	 *
	 * @param string $content generated content.
	 * @return string
	 */
	public function remove_siteorigin_inline_styles( $content ) {
		wp_dequeue_style( 'siteorigin-panels-front' );

		$so_panels = \SiteOrigin_Panels::single();
		remove_filter( 'wp_enqueue_scripts', array( $so_panels, 'generate_post_css' ) );
		remove_filter( 'wp_head', array( $so_panels, 'cached_post_css' ) );
		return $content;
	}

	/**
	 * Run before render starts.
	 *
	 * @param array $panels_data  Panels data array.
	 * @param int   $post_id      Post id.
	 *
	 * @return mixed
	 */
	public function panels_data( $panels_data, $post_id ) {
		// for some reason render is called twice, so we need to reset indexes at render start.
		$this->_row_index    = - 1;
		$this->_col_index    = - 1;
		$this->_cell_index   = - 1;
		$this->_widget_index = - 1;

		return $panels_data;
	}

	/**
	 * Main wrapper of the page builder content
	 *
	 * @param array   $attributes panel container attributes.
	 * @param integer $post_id post ID.
	 * @param array   $panels_data Panels data.
	 *
	 * @return array
	 */
	public function clean_panels_container_attributes( $attributes, $post_id, $panels_data ) {
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = 'pb-container';

		return $attributes;
	}

	/**
	 * Row wrapper hook callback
	 *
	 * @param array $attributes row wrapper div attributes.
	 * @param array $panel_data panel data array.
	 *
	 * @return mixed
	 */
	public function clean_row_attributes( $attributes, $panel_data ) {
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}

		if ( $this->index_elements ) {
			$attributes['class'] = 'pb-row pb-row-cols-' . count( $panel_data['cells'] );
		} else {
			$attributes['class'] = 'pb-row';
		}

		return $attributes;
	}

	/**
	 * Row hook callback
	 * called before row attributes hooks.
	 *
	 * @param array $attributes row div attributes.
	 * @param array $style_data row style settings array.
	 *
	 * @return mixed
	 */
	public function clean_row_inner_attributes( $attributes, $style_data ) {
		$this->_row_index ++;
		$this->_col_index = - 1;

		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}
		$attributes['class'] = array( 'pb-row-inner' );

		return $attributes;
	}

	/**
	 * Cell wrapper hook callback
	 *
	 * @param array $attributes row wrapper div attributes.
	 * @param array $panel_data panel data array.
	 *
	 * @return mixed
	 */
	public function clean_cell_attributes( $attributes, $panel_data ) {
		$this->_col_index ++;
		$this->_cell_index ++;
		$this->_widget_index = - 1;

		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}

		if ( $this->index_elements ) {
			$attributes['class'] = 'pb-cell pb-cell-num-' . ( $this->_col_index + 1 );
		} else {
			$attributes['class'] = 'pb-cell';
		}

		return $attributes;
	}

	/**
	 * Cell hook callback
	 *
	 * @param array $attributes row div attributes.
	 * @param array $style_data row style settings array.
	 *
	 * @return mixed
	 */
	public function clean_cell_inner_attributes( $attributes, $style_data ) {
		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}

		if ( $this->index_elements ) {
			$attributes['class'] = array( 'pb-cell-inner' );
		}

		return $attributes;
	}

	/**
	 * Remove hard-coded classes which are not controlled with wrapper attributes filter
	 *
	 * @param array $classes container html classes.
	 *
	 * @return array
	 */
	public function clean_css_classes( $classes ) {
		foreach ( $classes as $key => $class ) {
			if ( preg_match( '/(panel-.+?-style)/i', $class ) ) {
				unset( $classes[ $key ] );
			}
		}

		return $classes;
	}

	/**
	 * Widget attributes hook callback
	 *
	 * @param array $attributes row wrapper div attributes.
	 * @param array $widget_info widget info array.
	 *
	 * @return mixed
	 */
	public function clean_widget_attributes( $attributes, $widget_info ) {

		if ( isset( $attributes['id'] ) ) {
			unset( $attributes['id'] );
		}

		$class               = preg_replace(
			'/(so-panel\s|widget\s|panel\-|first-child|last-child)/',
			'',
			$attributes['class']
		);
		$attributes['class'] = 'pb-widget pb-widget-num-' . ( $this->_widget_index + 1 ) . ' ' . trim( $class );

		return $attributes;
	}

	/**
	 * Modify Widget wrapper classes (we can't modify any other attributes here)
	 * this method called first for widgets render.
	 *
	 * @param array  $classes html attribute.
	 * @param string $widget widget class.
	 *
	 * @return array
	 */
	public function clean_widget_inner_classes( $classes, $widget ) {
		$this->_widget_index ++;

		$classes['class'] = array( 'pb-widget-inner' );
		return $classes;
	}
}
