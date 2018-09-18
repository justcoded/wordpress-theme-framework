<?php

namespace JustCoded\WP\Framework\ACF;

use StoutLogic\AcfBuilder\Builder;
use StoutLogic\AcfBuilder\FieldBuilder;
use StoutLogic\AcfBuilder\FieldsBuilder;

trait Has_ACF_Fields {
	/**
	 * @var FieldsBuilder[]
	 */
	protected $fields;

	/**
	 * Add field definition to internal stack.
	 *
	 * @param FieldsBuilder ...$fields FieldsBuilder objects to be added.
	 */
	public function has( ...$fields ) {
		/* @var FieldsBuilder[] $args */
		$args = func_get_args();

		foreach ( $args as $group ) {
			$group = $group->getRootContext();

			$this->set_responsive_width_classes( $group->getFields() );

			$this->fields[ $group->getName() ] = $group;
		}
	}

	/**
	 * Update fields wrapper for responsive.
	 *
	 * @param FieldBuilder[] $fields Group fields.
	 */
	public function set_responsive_width_classes( $fields ) {
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$wrapper = $field->getWrapper();
				$width   = isset( $wrapper['width'] ) ? $wrapper['width'] : '100%';
				if ( false !== strpos( $width, '%' ) ) {
					$width = trim( $width, '%' );
					// Set attr class.
					$field->setAttr( 'class', 'jc_acf_fields jc_acf_fields_' . $width );
					// Set block layout for responsive.
					$field->setConfig( 'layout', 'block' );
					// Clear field width.
					$field->setWidth( '' );
				}
			}
		}
	}

	/**
	 * Build a fields group configuration object
	 *
	 * @param string|null $name         Group name.
	 * @param array       $group_config Group config.
	 *
	 * @return FieldsBuilder
	 */
	public function build( string $name = '', array $group_config = [] ) {
		// take current class short name as field name.
		if ( empty( $name ) ) {
			$class_name_parts = explode( '\\', get_class( $this ) );
			$class_short_name = strtolower( end( $class_name_parts ) );
			if ( ! isset( $this->fields[ $class_short_name ] ) ) {
				$name = $class_short_name;
			}
		}

		return new FieldsBuilder( $name, $group_config );
	}
}
