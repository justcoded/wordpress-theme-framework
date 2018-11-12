<?php


namespace JustCoded\WP\Framework\Objects;

/**
 * Class Cron
 * @package JustCoded\WP\Framework\Objects
 */
abstract class Cron {
	use Singleton;

	const FREQUENCY_MANUAL      = 'manual';
	const FREQUENCY_ONCE        = 'single';
	const FREQUENCY_HOURLY      = 'hourly';
	const FREQUENCY_TWICE_DAILY = 'twicedaily';
	const FREQUENCY_DAILY       = 'daily';

	/**
	 * @var string
	 */
	public $ID;

	/**
	 * @var int
	 */
	public $timestamp;

	/**
	 * @var string
	 */
	public $frequency;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var int
	 */
	public $interval;

	/**
	 * @var string
	 */
	public $schedule;

	/**
	 * Cronjob constructor.
	 */
	protected function __construct() {
		if ( empty( $this->ID ) ) {
			throw new \Exception( static::class . ' class: $ID property is required' );
		}

		// register action hook.
		add_action( $this->ID, [ $this, 'run' ] );

		if ( static::FREQUENCY_ONCE !== $this->frequency ) {
			// deactivation hook for repeatable event.
			add_action( 'switch_theme', [ $this, 'deactivate' ] );
		}

		if ( static::FREQUENCY_MANUAL === $this->schedule ) {
			if ( empty( $this->interval ) || ! is_numeric( $this->interval ) ) {
				throw new \Exception( static::class . ' class: $interval property is required and must be numeric if you use custom schedules' );
			}
			// register custom schedules.
			add_filter( 'cron_schedules', [ $this, 'register_custom_schedules' ] );
		}

		// register cron.
		add_action( 'init', [ $this, 'register' ] );

	}

	/**
	 * Registers cron with custom interval
	 */
	public function register_custom_schedules( $schedules ) {

		$schedules[ $this->frequency ] = array(
			'interval' => $this->interval,
			'display'  => $this->description ?? $this->ID . ' : ' . $this->frequency
		);

		return $schedules;
	}

	/**
	 * Registers cron with interval
	 */
	public function register() {
		if ( empty( $this->timestamp ) ) {
			$this->timestamp = time();
		} elseif ( ! is_numeric( $this->timestamp ) && is_string( $this->timestamp ) ) {
			$this->timestamp = strtotime( $this->timestamp );
		}

		if ( static::FREQUENCY_ONCE === $this->frequency ) {
			wp_schedule_single_event( $this->timestamp, $this->ID );
		} elseif ( ! wp_next_scheduled( $this->ID ) ) {
			wp_schedule_event( $this->timestamp, $this->frequency, $this->ID );
		}

	}

	/**
	 * Deactivate cron on theme deactivate.
	 */
	public function deactivate() {
		if ( $timestamp = wp_next_scheduled( $this->ID ) ) {
			wp_unschedule_event( $timestamp, $this->ID );
		}
	}

	/**
	 * Run action
	 */
	abstract public function run();
}