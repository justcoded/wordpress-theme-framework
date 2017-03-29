<?php
namespace JustCoded\ThemeFramework\SOPanels;

/**
 * Class WidgetLayout
 *
 * @package JustCoded\ThemeFramework\SOPanels
 */
class WidgetLayout {
	/**
	 * Widget Layout identifier
	 * should be overwritten in child class
	 *
	 * @var string
	 */
	public static $ID;

	/**
	 * Widget Layout display name
	 * should be overwritten in child class
	 *
	 * @var string
	 */
	public static $TITLE;

	/**
	 * Widget index in cell
	 *
	 * @var integer
	 */
	public $widget_index;

	/**
	 * WidgetLayout constructor.
	 *
	 * @throws \Exception Missing $ID or $TITLE properties.
	 */
	public function __construct() {
		if ( empty( $this::$ID ) || empty( $this::$TITLE ) ) {
			throw new \Exception( 'Register Layout failed for ' . get_class( $this ) . ': $ID, $TITLE static properties should be set!' );
		}
	}

	/**
	 * Replace wrapper classes string
	 *
	 * @param array  $classes html attribute.
	 * @param array  $style_data selected style options.
	 * @param string $widget widget class name.
	 * @param array  $instance widget data.
	 *
	 * @return array
	 */
	public function wrapper_classes( array $classes, $style_data, $widget, $instance ) {
		return $classes;
	}

	/**
	 * Adjust html attributes widget row div
	 *
	 * @param array $attributes  html attributes.
	 * @param array $style_data  widget settings.
	 *
	 * @return array    update attributes
	 */
	public function widget( $attributes, $style_data ) {
		return $attributes;
	}

}
