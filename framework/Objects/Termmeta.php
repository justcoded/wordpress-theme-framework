<?php

namespace JustCoded\WP\Framework\Objects;

class Termmeta extends Meta_Fields {

	/**
	 * Internal cache of wp queries
	 *
	 * @var \WP_Query[]
	 */
	private $_queries = [];

	/**
	 * Internal cache for post custom fields data
	 *
	 * @var array
	 */
	protected $_fields = [];

	/**
	 * Postmeta constructor.
	 */
	public function __construct() {
		// set current post for new created instance.
		$this->set_term( null );
	}

	/**
	 * Set $term property correctly
	 *
	 * @param \WP_Term|int|null $term Term object, id or null to take current object.
	 */
	protected function set_term( $term = null ) {
		if ( is_null( $term ) ) {
			$term = get_queried_object();
		}
		$this->term = $term;
	}

	/**
	 * Clean up queries cache in case you need to run new query
	 */
	protected function reset_queries() {
		$this->_queries = [];
	}

}
