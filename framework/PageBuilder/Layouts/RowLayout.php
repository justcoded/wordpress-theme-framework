<?php
namespace JustCoded\ThemeFramework\PageBuilder\Layouts;

/**
 * Class RowLayout
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class RowLayout {
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
	 * RowLayout constructor.
	 *
	 * @throws \Exception Missing $ID or $TITLE properties.
	 */
	public function __construct() {
		if ( empty( $this::$ID ) || empty( $this::$TITLE ) ) {
			throw new \Exception( 'Register Layout failed for ' . get_class( $this ) . ': $ID, $TITLE static properties should be set!' );
		}
	}

	/**
	 * Adjust html attributes for row wrapper div
	 *
	 * @param array $attributes  container attributes.
	 * @param array $panel_data  panel data settings.
	 *
	 * @return array    update attributes
	 */
	public function row_wrapper( $attributes, $panel_data ) {
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
	public function row( $attributes, $style_data ) {
		return $attributes;
	}

	/**
	 * Adjust html attributes for cell wrapper
	 *
	 * @param array $attributes  container attributes.
	 * @param array $panel_data  panel data settings.
	 *
	 * @return array    update attributes
	 */
	public function cell_wrapper( $attributes, $panel_data ) {
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
	public function cell( $attributes, $style_data ) {
		return $attributes;
	}

	/**
	 * Adjust custom html which can be inserted before row
	 * Default is null
	 *
	 * @param string $html  Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data  grid data options.
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
	 * @param string $html  Row HTML.
	 * @param array  $panel_data panel data array.
	 * @param array  $grid_data  grid data options.
	 *
	 * @return string
	 */
	public function after_row( $html, $panel_data, $grid_data ) {
		return $html;
	}
}
