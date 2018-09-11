<?php

namespace JustCoded\WP\Framework\ACF;

use StoutLogic\AcfBuilder\Builder;
use StoutLogic\AcfBuilder\FieldBuilder;
use StoutLogic\AcfBuilder\FieldsBuilder;

trait Has_ACF_Fields {
	/**
	 * @var FieldBuilder[]
	 */
	protected $fields;

	/**
	 * Add field definition to internal stack.
	 *
	 * @param FieldBuilder ...$fields FieldsBuilder objects to be added.
	 */
	public function has( ...$fields ) {
		/* @var FieldBuilder[] $args */
		$args = func_get_args();

		foreach ( $args as $field ) {
			$field = $field->getRootContext();
			// TODO: preprocess widths into classes.
			$this->fields[ $field->getName() ] = $field;
		}
	}

	/**
	 * Build a fields group configuration object
	 *
	 * @param string|null $name Group name.
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
