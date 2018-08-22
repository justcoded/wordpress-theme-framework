<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Layouts;

/**
 * Class Widget_Layout
 *
 * @package JustCoded\WP\Framework\SOPanels
 */
class Widget_Layout extends Layout {
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
	 * Unique widget index.
	 *
	 * @var integer
	 */
	public static $unique_index;

	/**
	 * Widget_Layout constructor.
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
	 *                'priority' => 15,        // order weight
	 *            ),
	 *
	 *
	 * By default we unset Design and Theme tabs. (1 assigned to values, because of empty() check in SO plugin)
	 *
	 * @return array
	 */
	public function options() {
		return array(
			'background'                  => 'disable',
			'background_image_attachment' => 'disable',
			'background_display'          => 'disable',
		);
	}

	/**
	 * Adjust html attributes widget row div
	 *
	 * @param array $attributes html attributes.
	 * @param array $style_data widget settings.
	 *
	 * @return array    update attributes
	 */
	public function widget( $attributes, $style_data ) {
		$style_class = 'pb-style-w' . ( (int) self::$unique_index ++ );
		if ( ! empty( $style_data['background_image_attachment'] ) ) {
			$attributes['class'][] = $style_class;
		}

		$attributes['style'] = $this->generate_inline_styles( $style_data, $style_class );

		return $attributes;
	}

	/**
	 * Replace inner div classes string
	 *
	 * @param array  $classes html attribute.
	 * @param array  $style_data selected style options.
	 * @param string $widget widget class name.
	 * @param array  $instance widget data.
	 *
	 * @return array
	 */
	public function widget_inner_classes( array $classes, $style_data, $widget, $instance ) {
		return $classes;
	}

}
