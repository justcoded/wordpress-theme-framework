<?php
namespace JustCoded\WP\Framework\Objects;

use JustCoded\WP\Framework\Web\Views_Rule;
use JustCoded\WP\Framework\Web\View;

/**
 * Custom Taxonomy class to simplify the process of registering Taxonomy.
 * Options are rewritten to be more simple for understanding.
 * However they affect all standard options from here:
 * https://codex.wordpress.org/Function_Reference/register_taxonomy
 */
abstract class Taxonomy {
	use Singleton;

	/**
	 * Taxonomy ID. Used to store it in DB and identify by taxonomy key
	 * SHOULD BE OVERWRITTEN IN CHILD CLASS
	 *
	 * @var string
	 */
	public static $ID;
	/**
	 * Taxonomy rewrite prefix, called slug. Will be present in URL structure
	 * SHOULD BE OVERWRITTEN IN CHILD CLASS
	 *
	 * @var string
	 */
	public static $SLUG;


	/**
	 * Single Post Type label
	 *
	 * @var string
	 */
	protected $label_singular;

	/**
	 * Multiple Post Type label
	 *
	 * @var string
	 */
	protected $label_multiple;

	/**
	 * Post type IDs to be used with this taxonomy
	 * usage : register_taxonomy_for_object_type()
	 *
	 * @var array
	 */
	protected $post_types;

	/**
	 * Has single page or not
	 * affect: show_in_nav_menus, public
	 *
	 * @var boolean
	 */
	protected $has_single = false;

	/**
	 * Ability to set Parent
	 * affect: hierarchical
	 *
	 * @var boolean
	 */
	protected $is_hierarchical = false;

	/**
	 * Simplify URLs structure and remove "slug" prefix from URL
	 * affect: rewrite.with_front
	 *
	 * @var boolean
	 */
	protected $rewrite_singular = false;

	/**
	 * Allow hierarchical urls, defaults false
	 *
	 * @var bool
	 */
	protected $rewrite_hierarchical = false;

	/**
	 * Show in admin menu
	 * affect: show_in_menu, show_ui
	 *
	 * @var boolean
	 */
	protected $has_admin_menu = true;


	/**
	 * Optional properties to make full compatibility with standard taxonomy registration
	 */

	/**
	 * Ability to edit in quick edit in posts list
	 * affect: show_in_quick_edit
	 *
	 * @var boolean
	 */
	protected $is_quick_editable = true;

	/**
	 * Ability to show admin column in posts list
	 * affect: show_admin_column
	 *
	 * @var boolean
	 */
	protected $admin_posts_column = true;

	/**
	 * Enable Tag Cloud widget for this taxonomy
	 * affect: show_tagcloud
	 *
	 * @var boolean
	 */
	protected $has_tag_cloud = false;

	/**
	 * Labels array to override helper auto-generation
	 * https://codex.wordpress.org/Function_Reference/register_post_type#labels
	 * affect: labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Custom capability options
	 * https://codex.wordpress.org/Function_Reference/register_taxonomy#capabilities
	 * affect: capabilities
	 *
	 * @var null|array
	 */
	protected $capabilities = null;

	/**
	 * Custom query var to be applied for single pages
	 * https://codex.wordpress.org/Function_Reference/register_post_type#capabilities#query_var
	 * affect: query_var
	 *
	 * @var boolean
	 */
	protected $query_var = true;

	/**
	 * Textdomain to apply to labels on taxonomy registration
	 *
	 * @var string
	 */
	protected $textdomain = 'justcoded_theme_framework';

	/**
	 * Constructor
	 * Check required parameter ID and add WP action hook
	 *
	 * @throws \Exception Taxonomy $ID property not defined.
	 */
	protected function __construct() {
		if ( empty( $this::$ID ) ) {
			throw new \Exception( 'Taxonomy "' . get_class( $this ) . '" init failed: missing $ID property' );
		}

		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'template_include', array( $this, 'views' ), 10 );
	}

	/**
	 * Declaration of the 'init' action hook
	 * should call $this->register( params ) inside
	 */
	abstract public function init();

	/**
	 * Main function to register taxonomy.
	 * Convert class properties to array of args for register_taxonomy
	 */
	public function register() {
		$labels = array(
			'name'          => _x( $this->label_multiple, 'taxonomy general name', $this->textdomain ),
			'singular_name' => _x( $this->label_singular, 'taxonomy singular name', $this->textdomain ),
		);

		$args = array(
			'labels'             => $labels,
			'hierarchical'       => $this->is_hierarchical,
			'public'             => $this->has_single,
			'show_in_nav_menus'  => $this->has_single,
			'show_tagcloud'      => $this->has_tag_cloud,
			'show_ui'            => $this->has_admin_menu,
			'show_admin_column'  => $this->admin_posts_column,
			'show_in_quick_edit' => $this->is_quick_editable,
			'query_var'          => $this->query_var,
			'rewrite'            => array(
				'slug'         => $this::$SLUG,
				'with_front'   => ! $this->rewrite_singular,
				'hierarchical' => $this->rewrite_hierarchical,
			),
		);

		if ( ! empty( $this->capabilities ) ) {
			$args['capabilities'] = $this->capabilities;
		}

		register_taxonomy( $this::$ID, null, $args );

		// init relations.
		if ( ! empty( $this->post_types ) && is_array( $this->post_types ) ) {
			foreach ( $this->post_types as $post_type ) {
				register_taxonomy_for_object_type( $this::$ID, $post_type );
			}
		}
	}

	/**
	 * Apply custom rules to template loader.
	 * By default point all inner CPT pages to views/{cpt}/{this::$ID}.php
	 *
	 * @param string $template  Standard template file to be loaded.
	 *
	 * @return string Modified template file path
	 */
	public function views( $template ) {
		if ( is_tax( $this::$ID ) ) {
			$templates = array();

			// if we have taxonomy related with post_type by query var - we set this post type a priority.
			if ( $post_type = get_query_var( 'post_type' ) ) {
				if ( is_array( $post_type ) ) {
					$post_type = reset( $post_type );
				}
				$templates[] = "views/$post_type/taxonomy-" . $this::$ID . '.php';
			}

			// search tax template inside all post types related to taxonomy.
			foreach ( $this->post_types as $post_type ) {
				$templates[] = "views/$post_type/taxonomy-" . $this::$ID . '.php';
			}

			$templates = array_unique( $templates );

			if ( $tax_template = locate_template( $templates ) ) {
				$template = $tax_template;
			}
		}

		return $template;
	}
}
