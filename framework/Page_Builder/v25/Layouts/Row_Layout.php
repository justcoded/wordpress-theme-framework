<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Layouts;

/**
 * Class Row_Layout
 *
 * @package JustCoded\WP\Framework\SOPanels
 */
class Row_Layout extends Layout {
	/**
	 * Row Layout identifier
	 * should be overwritten in child class
	 *
	 * @var string
	 */
	public static $ID;

	/**
	 * Row Layout display name
	 * should be overwritten in child class
	 *
	 * @var string
	 */
	public static $TITLE;

	/**
	 * Current row index
	 *
	 * @var integer
	 */
	public $row_index;

	/**
	 * Current col in row index
	 *
	 * @var integer
	 */
	public $col_index;

	/**
	 * Current cell index
	 *
	 * @var integer
	 */
	public $cell_index;

	/**
	 * Row_Layout constructor.
	 *
	 * @throws \Exception Missing $ID or $TITLE properties.
	 */
	public function __construct() {
		if ( empty( $this::$ID ) || empty( $this::$TITLE ) ) {
			throw new \Exception( 'Register Layout failed for ' . get_class( $this ) . ': $ID, $TITLE static properties should be set!' );
		}
	}

	/**
	 * Additional options to add into Row option controls
	 *
	 * Field element should has a format similar to this:
	 *
	 *      '{field}' => array(
	 *                'name'     => '{Field Title}',
	 *                'type'     => 'select',  // available: text, select, color, measurement
	 *                'group'    => 'layout',  // available: attributes, layout, design
	 *                'options'  => array(     // select options
	 *                    'No',
	 *                    'Yes',
	 *                ),
	 *                'priority' => 15,        // order weigth
	 *            ),
	 *
	 * By default we unset Design and Theme tabs. (1 assigned to values, because of empty() check in SO plugin)
	 *
	 * @return array
	 */
	public function options() {
		return array(
			'mobile_padding'              => 1,
			'background'                  => 1,
			'background_image_attachment' => 1,
			'background_display'          => 1,
			'border_color'                => 1,
			'id'                          => 1,
			'class'                       => 1,
			'row_css'                     => 1,
			'mobile_css'                  => 1,
			'padding'                     => 1,
			'cell_class'                  => 1,
			'bottom_margin'               => 1,
			'gutter'                      => 1,
			'row_stretch'                 => 1,
			'collapse_behaviour'          => 1,
			'collapse_order'              => 1,
			'cell_alignment'              => 1,
			'widget_css'                  => 1,
			'font_color'                  => 1,
			'link_color'                  => 1,
			'hero_size'                   => 1,
		);
	}

	/**
	 * Adjust html attributes for row wrapper div
	 *
	 * @param array $attributes container attributes.
	 * @param array $panel_data panel data settings.
	 *
	 * @return array    update attributes
	 */
	public function row( $attributes, $panel_data ) {
		return $attributes;
	}

	/**
	 * Adjust html attributes for row div
	 *
	 * @param array $attributes container attributes.
	 * @param array $style_data row settings.
	 *
	 * @return array    update attributes
	 */
	public function row_inner( $attributes, $style_data ) {
		$style_class = "pb-style-r{$this->row_index}";
		if ( ! empty( $style_data['background_image_attachment'] ) ) {
			$attributes['class'][] = $style_class;
		}

		return $attributes;
	}

	/**
	 * Adjust html attributes for cell wrapper
	 *
	 * @param array $attributes container attributes.
	 * @param array $panel_data panel data settings.
	 *
	 * @return array    update attributes
	 */
	public function cell( $attributes, $panel_data ) {
		return $attributes;
	}

	/**
	 * Adjust html attributes for cell
	 *
	 * @param array $attributes container attributes.
	 * @param array $style_data row settings.
	 *
	 * @return array    update attributes
	 */
	public function cell_inner( $attributes, $style_data ) {
		return $attributes;
	}

	/**
	 * Adjust custom html which can be inserted before row
	 * Default is null
	 *
	 * @param string $html Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data grid data options.
	 *
	 * @return string
	 */
	public function before_row( $html, $panel_data, $grid_data ) {
		return $html;
	}

	/**
	 * Adjust custom html which can be inserted after row
	 * Default is null
	 *
	 * @param string $html Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data grid data options.
	 *
	 * @return string
	 */
	public function after_row( $html, $panel_data, $grid_data ) {
		return $html;
	}
}
