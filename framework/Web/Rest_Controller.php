<?php

namespace JustCoded\WP\Framework\Web;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class RestController
 *
 * @package JustCoded\WP\Framework\Objects
 *
 * @method Rest_Controller instance() static
 */
abstract class Rest_Controller extends \WP_REST_Controller {
	use Singleton;

	/**
	 * Basic namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'ext/v2';

	/**
	 * Resource name.
	 * Should be set inside child class.
	 *
	 * @var string
	 */
	protected $resource_name;

	/**
	 * Custom routes.
	 *
	 * @var array
	 */
	private $routes = [];

	/**
	 * Constructor
	 * Check required parameter ROUTE and add WP action hook
	 *
	 * @throws \Exception Missing $resource_name property.
	 */
	protected function __construct() {
		if ( empty( $this->resource_name ) ) {
			throw new \Exception( static::class . '" init failed: missing resource_name property' );
		}

		$this->init();

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Add Route function.
	 *
	 * @param string $alias Name of a key in route array.
	 * @param string $method HTTP method.
	 * @param string $route Name of route.
	 * @param array  $callback Callback function.
	 * @param array  $args Args array.
	 * @param array  $permission_callback Permission callback function.
	 */
	protected function add_route( $alias, $method, $route, $callback, $args = [], $permission_callback = [] ) {
		$this->routes[ $alias ] = [
			'route' => $route,
			'args'  => [
				[
					'methods'             => $method,
					'callback'            => $callback,
					'permission_callback' => $permission_callback,
					'args'                => $args,
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			],
		];
	}

	/**
	 * Register_endpoint
	 */
	public function register_routes() {
		foreach ( $this->routes as $route ) {
			register_rest_route(
				$this->namespace,
				$this->resource_name . $route['route'],
				$route['args']
			);
		}
	}

	/**
	 * Response
	 *
	 * @param mixed $data Data.
	 * @param int   $status HTTP status.
	 * @param array $headers HTTP headers.
	 *
	 * @return \WP_REST_Response
	 */
	protected function response( $data, $status = 200, $headers = [] ) {
		return new \WP_REST_Response( $data, $status, $headers );
	}

	/**
	 * Declaration of the 'init' action hook
	 * should call $this->add_route( $method, $route, $alias, $callback, $args, $permission_callback ) inside
	 *
	 * @see Rest_Controller::add_route() Add route function.
	 *
	 * @return void
	 */
	abstract public function init();

	/**
	 * Get_permalink
	 *
	 * @param string $slug_key .
	 * @param array  $args .
	 *
	 * @return string|bool
	 */
	public static function get_permalink( $alias, $args = array() ) {
		if ( is_array( $alias ) ) {
			return false;
		}

		$instance = static::instance();

		$data       = [];
		$route      = $instance->routes[ $alias ]['route'];
		$slug_regex = str_replace( [ '(', ')' ], [ '_*(', ')*_' ], $route );
		$rest_url   = rest_url( $instance->namespace . '/' . $instance->resource_name );


		if ( ! preg_match_all( '/<(\w+)>/i', $route, $args_matches ) ) {
			return $rest_url;
		}

		if ( ! preg_match_all( '/_\*(.*?)\*_/i', $slug_regex, $data_matches ) ) {
			return $rest_url;
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
				$route = str_replace( $match, $data[ $key_match[1] ], $route );
			}
		}

		return $rest_url . $route . $query_args;
	}
}
