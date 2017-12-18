<?php

namespace JustCoded\WP\Framework\Objects;

class Postmeta extends Meta_Fields {

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
		$this->set_post( null );
	}

	/**
	 * Set $post property correctly
	 *
	 * @param \WP_Post|int|null $post Post object, id or null to take current object.
	 */
	protected function set_post( $post = null ) {
		if ( is_null( $post ) ) {
			$post = get_the_ID();
		}
		$this->post = get_post( $post );
	}

	/**
	 * Clean up queries cache in case you need to run new query
	 */
	protected function reset_queries() {
		$this->_queries = [];
	}

}
