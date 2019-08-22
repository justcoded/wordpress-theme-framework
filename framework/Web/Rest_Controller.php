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
	protected $routes = [];

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
	 * Update_acf_value
	 *
	 * @param int   $post_id Post ID.
	 * @param array $args Arguments.
	 *
	 * @return \WP_Error|bool
	 */
	protected function update_acf_fields( $post_id, $args ) {
		if ( empty( $args ) ) {
			return new \WP_Error( 'rest_employee_empty_args', __( 'Empty custom args.' ), array( 'status' => 500 ) );
		}

		foreach ( $args as $selector => $value ) {
			update_field( $selector, $value, $post_id );
		}

		return true;
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
	 * Get permalink
	 *
	 * @param string $alias .
	 * @param array  $args .
	 *
	 * @return string
	 */
	public static function get_permalink( $alias, $args = array() ) {
		if ( is_array( $alias ) ) {
			return false;
		}

		$instance = static::instance();

		$data         = [];
		$route        = $instance->routes[ $alias ]['route'];
		$slug_convert = preg_replace( '/(\(\?\w+<(\w+)>(.*?)\))/i', '{$2}', $route );
		$rest_url     = rest_url( $instance->namespace . '/' . $instance->resource_name );

		foreach ( $args as $key => $value ) {
			if ( false === strpos( $slug_convert, $key ) ) {
				continue;
			}
			$data[ '{' . $key . '}' ] = $value;
			unset( $args[ $key ] );
		}

		$route    = strtr( $slug_convert, $data );
		$rest_url = add_query_arg( [ $args ], $rest_url . $route );

		return $rest_url;
	}
}
