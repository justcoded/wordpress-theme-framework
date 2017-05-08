<?php
namespace JustCoded\ThemeFramework\SOPanels;


class PluginLoader25 extends PluginLoaderBase {
	/**
	 * Modify Widget object fields, which are shown at the right of widget editing
	 *
	 * @param array $fields standard fields options.
	 *
	 * @return array modified fields options.
	 */
	public function update_widget_style_fields( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'widget_css',
			'class',
			// layout.
			'bottom_margin',
			'gutter',
			'padding',
			'row_stretch',
			'font_color',
			'link_color',
			'mobile_padding',
			'collapse_order',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		// register custom Layout selector.
		$widgets                   = $this->get_list_widgets();
		$fields['widget_template'] = array(
			'name'     => 'Widget layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $widgets,
			'priority' => 10,
		);

		return $fields;
	}

	/**
	 * Adjust Page Builder Layout group options.
	 * Remove standard fields for margin/gutter/padding/strech.
	 * Add custom Row Layout which will affect row/cell classes
	 *
	 * @param array $fields siteorigin standard fields.
	 *
	 * @return array modified fields.
	 */
	public function update_row_style_fields( $fields ) {
		// unset SiteOrigin Standard attributes.
		$unset_fields = array(
			// attributes.
			'class',
			'cell_class',
			'row_css',
			'id',
			// layout.
			'bottom_margin',
			'gutter',
			'padding',
			'row_stretch',
			'mobile_padding',
			'collapse_order',
		);
		foreach ( $unset_fields as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				unset( $fields[ $field ] );
			}
		}

		// register custom Layout selector.
		$layouts                = $this->get_list_layouts();
		$fields['row_template'] = array(
			'name'     => 'Row layout',
			'type'     => 'select',
			'group'    => 'layout',
			'options'  => $layouts,
			'priority' => 10,
		);

		return $fields;
	}
}