<?php
namespace JustCoded\ThemeFramework\PageBuilder\Layouts;

/**
 * Class Grid12RowLayout
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class RwdRowLayout extends RowLayout {
	/**
	 * ID
	 *
	 * @var string
	 */
	public static $ID = 'grid12';

	/**
	 * Title
	 *
	 * @var string
	 */
	public static $TITLE = 'Responsive Columns';

	/**
	 * Grid max columns size
	 *
	 * @var int
	 */
	public $grid_size_columns = 12;

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

		$cell_postfix    = round( $this->grid_size_columns * $cell['weight'] );
		$bootstrap_class = 'pc-col-sz-' . $cell_postfix;

		$attributes['class'] .= ' ' . $bootstrap_class;

		return $attributes;
	}

}
