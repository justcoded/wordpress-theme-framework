<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Class Cronjob
 *
 * @package JustCoded\WP\Framework\Objects
 *
 * @method Cronjob instance() static
 */
abstract class Cronjob {
	use Singleton;

	/**
	 * Constant for single cron.
	 */
	const FREQUENCY_ONCE = 'single';

	/**
	 * Constant for hourly cron.
	 */
	const FREQUENCY_HOURLY = 'hourly';

	/**
	 * Constant for twicedaily cron.
	 */
	const FREQUENCY_TWICEDAILY = 'twicedaily';

	/**
	 * Constant for daily cron.
	 */
	const FREQUENCY_DAILY = 'daily';

	/**
	 * Cron id.
	 *
	 * @var string
	 */
	protected $ID;

	/**
	 * Start time.
	 *
	 * @var int|string
	 */
	protected $start;

	/**
	 * Schedule name.
	 *
	 * @var string
	 */
	protected $schedule;

	/**
	 * Cronjob schedule description.
	 *
	 * @var string
	 */
	protected $schedule_description;

	/**
	 * Interval in seconds.
	 *
	 * @var int
	 */
	protected $interval;

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
		add_action( $this->ID, [ $this, 'handle' ] );

		$this->cron_debug();

		if ( static::FREQUENCY_ONCE !== $this->schedule ) {
			// deactivation hook for repeatable event.
			add_action( 'switch_theme', [ $this, 'deactivate' ] );
		}

		if ( ! in_array( $this->schedule, $this->get_all_frequency(), true ) ) {
			if ( empty( $this->interval ) || ! is_numeric( $this->interval ) ) {
				throw new \Exception( static::class . ' class: $interval property is required and must be numeric if you use a custom schedule' );
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
		$schedule[ $this->schedule ] = [
			'interval' => $this->interval,
			'display'  => $this->schedule_description ?? $this->ID . ' : ' . $this->schedule,
		];

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
	 * Get all frequency
	 *
	 * @return array
	 */
	protected function get_all_frequency() {
		return [
			self::FREQUENCY_HOURLY,
			self::FREQUENCY_TWICEDAILY,
			self::FREQUENCY_DAILY,
		];
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

		if ( ! isset( $_GET['do_cron'] ) || $_GET['do_cron'] !== $this->ID ) {
			return false;
		}

		remove_action( $this->ID, [ $this, 'handle' ] );
		add_action( 'init', [ $this, 'handle' ] );

		return true;
	}

	/**
	 * Handle function
	 */
	abstract public function handle();
}
