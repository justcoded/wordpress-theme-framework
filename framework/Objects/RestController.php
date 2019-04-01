<?php

namespace JustCoded\WP\Framework\Objects;


abstract class RestController {
	use Singleton;

	static $namespace = 'api';

	protected $routes = [];

	/**
	 * Constructor
	 * Check required parameter ROUTE and add WP action hook
	 *
	 * @throws \Exception Missing $ROUTE property.
	 */
	protected function __construct() {
		echo self::$namespace;
		if ( empty( self::$namespace ) ) {
			throw new \Exception( 'REST ENDPOINT "' . get_class( $this ) . '" init failed: missing namespace property' );
		}

		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );

	}

	public static function get_permalink( $slug, $args = array() ) {
		$slug = str_replace( [ '(', ')' ], [ '(\(', '\))' ], $slug );
		echo $slug;
		//$slug = preg_replace($slug, $args)
		echo home_url( '/wp-json/' . self::$namespace );
	}

	public function register_endpoint() {
		$this->init();
		if ( empty( $this->routes ) ) {
			throw new \Exception( 'REST ENDPOINT "' . get_class( $this ) . '" init failed: missing routes property' );
		}
		foreach ( $this->routes as $route ) {
			register_rest_route(
				self::$namespace,
				$route['route'],
				$route['args']
			);
		}
	}

	/**
	 * Declaration of the 'init' action hook
	 * should call $this->add_route( $method, $route, $callback, $args ) inside
	 */
	abstract public function init();

	protected function add_route( $method = 'POST', $route, $callback, $args = [] ) {
		if ( empty( $route ) ) {
			return;
		}

		$this->routes[] = [
			'route' => $route,
			'args'  => [
				'methods'  => $method,
				'callback' => $callback,
				'args'     => $args
			]
		];
	}

}