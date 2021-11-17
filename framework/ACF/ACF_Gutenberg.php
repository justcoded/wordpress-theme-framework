<?php

namespace JustCoded\WP\Framework\ACF;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class ACF_Gutenberg
 */
abstract class ACF_Gutenberg {
	use Singleton;

	/**
	 * Block Slug Name
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Block Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Block Description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Block Icon Name
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * Block Keywords
	 *
	 * @var array
	 */
	public $keywords;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'init' ) );
	}

	/**
	 * Action hook 'init'
	 * call $this->register_block()
	 */
	abstract public function init();


	/**
	 * Register ACF Gutenberg Block.
	 */
	public function register_block(): void {
		$block_defaults = array(
			'category' => 'formatting',
			'icon'     => 'admin-generic',
		);

		$block = array(
			'name'            => $this->slug,
			'title'           => $this->title,
			'description'     => $this->description,
			'icon'            => $this->icon,
			'keywords'        => $this->keywords,
			'render_template' => get_stylesheet_directory() . '/views/blocks/' . $this->slug . '.php',
		);

		$block = wp_parse_args( $block, $block_defaults );

		acf_register_block( $block );

		if ( isset( $block['fields'] ) ) {
			acf_add_local_field_group( $block['fields'] );
		}
	}
}
