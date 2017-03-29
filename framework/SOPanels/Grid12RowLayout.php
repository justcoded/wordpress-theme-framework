<?php
namespace JustCoded\ThemeFramework\SOPanels;

/**
 * Class Grid12RowLayout
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class Grid12RowLayout extends RowLayout {
	/**
	 * ID
	 *
	 * @var string
	 */
	public static $ID = 'grid-12';

	/**
	 * Title
	 *
	 * @var string
	 */
	public static $TITLE = 'Auto Grid: 12 columns';

	/**
	 * Adjust html attributes for cell wrapper
	 *
	 * @param array $attributes  container attributes.
	 * @param array $panel_data  panel data settings.
	 *
	 * @return array    update attributes
	 */
	public function cell_wrapper( $attributes, $panel_data ) {
		$cell = $panel_data['grid_cells'][ $this->cell_index ];
		$grid = $panel_data['grids'][ $cell['grid'] ];

		$cell_postfix    = round( 12 * $cell['weight'] );
		$bootstrap_class = 'jgrid-col-' . $cell_postfix;

		$attributes['class'] .= ' ' . $bootstrap_class;

		return $attributes;
	}

}
