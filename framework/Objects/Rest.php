<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Custom rest class to simplify the process of registering rest endpoints.
 * Options are rewritten to be more simple for understanding.
 * However they affect all standard options from here:
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */
abstract class Rest {
	use Singleton;

	const METHOD_GET  = 'GET';
	const METHOD_POST = 'POST';

	public static $ROUTE;

	public $method;

	public $validate_args = [];


	/**
	 * Constructor
	 * Check required parameter ROUTE and add WP action hook
	 *
	 * @throws \Exception Missing $ROUTE property.
	 */
	protected function __construct() {
		if ( empty( $this::$ROUTE ) ) {
			throw new \Exception( 'REST ENDPOINT "' . get_class( $this ) . '" init failed: missing $ROUTE property' );
		}
		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );

	}

	public function register_endpoint() {

		$this->init();

		register_rest_route(
			REST_NAMESPACE,
			$this::$ROUTE,
			[
				'methods'  => $this->method,
				'callback' => [ $this, 'callback' ],
				'args'     => $this->validate_args
			]
		);
	}

	/**
	 * Declaration of the 'init' action hook
	 */
	abstract public function init();

	/**
	 * Declaration of the 'callback' action hook
	 * should return json
	 */
	abstract public function callback( $request );

}