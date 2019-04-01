<?php


namespace JustCoded\WP\Framework\Objects;

/**
 * Class Cronjob
 * @package JustCoded\WP\Framework\Objects
 *
 * @method Cronjob instance() static
 */
abstract class Cronjob {
	use Singleton;

	/**
	 * Constant for single cron
	 */
	const FREQUENCY_ONCE = 'single';

	/**
	 * @var string
	 */
	protected $ID;

	/**
	 * @var int|string
	 */
	protected $start;

	/**
	 * @var string
	 */
	protected $schedule;

	/**
	 * @var string
	 */
	protected $schedule_description;

	/**
	 * @var int
	 */
	protected $interval;

	/**
	 * @var bool $debug Debug status. Default 'false'. Accepts 'true', 'false'.
	 */
	protected $debug = false;

	/**
	 * @var string $debug_type Debug type. Default 'manual'. Accepts 'auto', 'manual'.
	 */
	protected $debug_type = 'manual';

	/**
	 * @var array
	 */
	private $frequency = [ 'hourly', 'twicedaily', 'daily' ];

	/**
	 * Cronjob constructor.
	 *
	 * @throws \Exception
	 */
	protected function __construct() {
		if ( empty( $this->ID ) ) {
			throw new \Exception( static::class . ' class: $ID property is required' );
		}

		// register action hook.
		add_action( $this->ID, [ $this, 'run' ] );

		// debug cronjob
		if ( isset( $this->debug ) && true === $this->debug ) {
			$this->cron_debug();
		}

		if ( static::FREQUENCY_ONCE !== $this->schedule ) {
			// deactivation hook for repeatable event.
			add_action( 'switch_theme', [ $this, 'deactivate' ] );
		}

		if ( ! in_array( $this->schedule, $this->frequency, true ) ) {
			if ( empty( $this->interval ) || ! is_numeric( $this->interval ) ) {
				throw new \Exception( static::class . ' class: $interval property is required and must be numeric if you use custom schedule' );
			}

			// register custom schedule.
			add_filter( 'cron_schedules', [ $this, 'register_custom_schedule' ], 99, 1 );
		}

		// register cron.
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register_custom_schedule
	 *
	 * @param array $schedule Non-default schedule.
	 *
	 * @return array
	 */
	public function register_custom_schedule( $schedule ) {
		$schedule[ $this->schedule ] = array(
			'interval' => $this->interval,
			'display'  => $this->schedule_description ?? $this->ID . ' : ' . $this->schedule
		);

		return $schedule;
	}

	/**
	 * Registers cron with interval
	 */
	public function register() {
		if ( empty( $this->start ) ) {
			$this->start = time();
		} elseif ( ! is_numeric( $this->start ) && is_string( $this->start ) ) {
			$this->start = strtotime( $this->start );
		}

		if ( static::FREQUENCY_ONCE === $this->schedule ) {
			wp_schedule_single_event( $this->start, $this->ID );
		} elseif ( ! wp_next_scheduled( $this->ID ) ) {
			wp_schedule_event( $this->start, $this->schedule, $this->ID );
		}
	}

	/**
	 * Deactivate cron on theme deactivate.
	 */
	public function deactivate() {
		if ( $start = wp_next_scheduled( $this->ID ) ) {
			wp_unschedule_event( $start, $this->ID );
		}
	}

	/**
	 * Get_cron_data
	 *
	 * @return array
	 */
	protected function get_cron_data() {
		$crons     = _get_cron_array();
		$cron_data = [];

		foreach ( $crons as $time => $cron ) {
			foreach ( $cron as $hook => $dings ) {

				if ( $this->ID !== $hook ) {
					continue;
				}

				foreach ( $dings as $sig => $data ) {
					$cron_data[] = (object) array(
						'hook'     => $hook,
						'time'     => $time,
						'sig'      => $sig,
						'args'     => $data['args'],
						'schedule' => $data['schedule'],
						'interval' => isset( $data['interval'] ) ? $data['interval'] : null,
					);
				}
			}
		}

		return $cron_data;
	}

	/**
	 * Debug
	 *
	 * @return bool
	 */
	protected function cron_debug() {
		if ( ! defined( 'WP_DEBUG' ) || false === WP_DEBUG ) {
			return false;
		}

		// Check type of debug.
		if ( 'manual' === $this->debug_type ) {
			remove_action( $this->ID, [ $this, 'run' ] );
			add_action( 'init', [ $this, 'run' ] );

			return true;
		}

		$cron_data = $this->get_cron_data();

		if ( empty( $cron_data ) || count( $cron_data ) > 1 ) {
			return false;
		}

		delete_transient( 'doing_cron' );
		wp_schedule_single_event( time() - 1, $cron_data[0]->hook, $cron_data[0]->args );
		spawn_cron();

		return true;
	}

	/**
	 * Run action
	 */
	abstract public function run();
}