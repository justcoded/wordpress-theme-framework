<?php

namespace JustCoded\WP\Framework\ACF;


use JustCoded\WP\Framework\Objects\Singleton;

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
	public function register_block() {
		$block_defaults = array(
			'category' => 'formatting',
			'icon'     => 'admin-generic',
		);

		add_filter( 'jc_register_acf_gutenberg_block', function ( $blocks, $block_defaults ) {
			array_push( $blocks, array(
				'name'            => $this->slug,
				'title'           => $this->title,
				'description'     => $this->description,
				'icon'            => $this->icon,
				'keywords'        => $this->keywords,
				'render_template' => get_stylesheet_directory() . '/views/blocks/' . $this->slug . '.php',
			) );

			return $blocks;
		}, 10, 2 );

		$blocks = apply_filters( 'jc_register_acf_gutenberg_block', array(), $block_defaults );

		foreach ( $blocks as $block ) {
			$block = wp_parse_args( $block, $block_defaults );

			acf_register_block( $block );

			if ( isset( $block['fields'] ) ) {
				acf_add_local_field_group( $block['fields'] );
			}
		}
	}
}