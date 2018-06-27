<?php

namespace JustCoded\WP\Framework\Objects;

/**
 * Class Singleton
 * Singleton design pattern base class
 */
trait Singleton {
	/**
	 * Returns the *Singleton* current class.
	 *
	 * @return Singleton A single instance of this class.
	 */
	public static function instance() {
		static $instance = null;

		return $instance ?: $instance = new static();
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	final private function __clone() {
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	final private function __wakeup() {
	}

}
