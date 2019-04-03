<?php

namespace JustCoded\WP\Framework\Objects;


/**
 * Class RestController
 * @package JustCoded\WP\Framework\Objects
 *
 * @method RestController instance() static
 */
abstract class RestController {
	use Singleton;

	/**
	 * @var string
	 */
	protected $namespace = 'api';

	/**
	 * @var array
	 */
	protected $routes = [];

	/**
	 * @var array
	 */
	protected $slugs;

	/**
	 * Constructor
	 * Check required parameter ROUTE and add WP action hook
	 *
	 * @throws \Exception Missing $ROUTE property.
	 */
	protected function __construct() {
		if ( empty( $this->namespace ) ) {
			throw new \Exception( 'REST ENDPOINT "' . static::class . '" init failed: missing namespace property' );
		}

		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );
	}

	/**
	 * Get_permalink
	 *
	 * @param string $slug_key .
	 * @param array  $args .
	 *
	 * @return string|bool
	 */
	public function get_permalink( $slug_key, $args = array() ) {
		if ( is_array( $slug_key ) ) {
			return false;
		}

		$data       = [];
		$slug       = $this->slugs[ $slug_key ];
		$slug_regex = str_replace( [ '(', ')' ], [ '_*(', ')*_' ], $slug );
		$home_url   = home_url( '/wp-json/' . $this->namespace );

		if ( ! preg_match_all( '/<(\w+)>/i', $slug, $args_matches ) ) {
			return false;
		}

		if ( ! preg_match_all( '/_\*(.*?)\*_/i', $slug_regex, $data_matches ) ) {
			return false;
		}

		foreach ( $args_matches[1] as $match ) {
			if ( array_key_exists( $match, $args ) ) {
				$data[ $match ] = $args[ $match ];
				unset( $args[ $match ] );
			}
		}

		$query_args = add_query_arg( [ $args ] );

		foreach ( $data_matches[1] as $match ) {
			if ( ! preg_match( '/<(\w+)>/i', $match, $key_match ) ) {
				continue;
			}

			if ( ! empty( $data[ $key_match[1] ] ) ) {
				$slug = str_replace( $match, $data[ $key_match[1] ], $slug );
			}
		}

		return $home_url . $slug . $query_args;
	}

	/**
	 * Register_endpoint
	 *
	 * @throws \Exception
	 */
	public function register_endpoint() {
		$this->init();
		if ( empty( $this->routes ) ) {
			throw new \Exception( 'REST ENDPOINT "' . static::class . '" init failed: missing routes property' );
		}
		foreach ( $this->routes as $route ) {
			register_rest_route(
				$this->namespace,
				$route['route'],
				$route['args']
			);
		}
	}

	/**
	 * Add_route
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $callback
	 * @param array  $args
	 * @param array  $permission_callback
	 *
	 * @throws \Exception
	 */
	protected function add_route( $route, $method, $callback, $args = [], $permission_callback = [] ) {

		if ( empty( $route ) ) {
			throw new \Exception( static::class . ' class: $route property is required' );
		}

		$this->routes[] = [
			'route' => $route,
			'args'  => [
				'methods'             => $method,
				'callback'            => $callback,
				'permission_callback' => $permission_callback,
				'args'                => $args
			],
		];
	}

	/**
	 * Response
	 *
	 * @param mixed $data
	 * @param int   $status
	 * @param array $headers
	 *
	 * @return \WP_REST_Response
	 */
	protected function response( $data, $status = 200, $headers = [] ) {
		return new \WP_REST_Response( $data, $status, $headers );
	}

	/**
	 * Declaration of the 'init' action hook
	 * should call $this->add_route( $method, $route, $callback, $args ) inside
	 */
	abstract public function init();
}