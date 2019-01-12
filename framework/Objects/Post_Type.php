<?php

namespace JustCoded\WP\Framework\Objects;

use JustCoded\WP\Framework\Supports\FakerPress;

/**
 * Custom post type class to simplify the process of registering post type.
 * Options are rewritten to be more simple for understanding.
 * However they affect all standard options from here:
 * https://codex.wordpress.org/Function_Reference/register_post_type
 */
abstract class Post_Type {
	use Singleton;

	/**
	 * Post_Type ID. Used to store it in DB and identify by post_type key
	 * SHOULD BE OVERWRITTEN IN CHILD CLASS
	 *
	 * @var string
	 */
	public static $ID;

	/**
	 * Post_Type rewrite prefix, called slug. Will be present in URL structure
	 * SHOULD BE OVERWRITTEN IN CHILD CLASS
	 *
	 * @var string
	 */
	public static $SLUG;

	const STATUS_ANY        = 'any';
	const STATUS_DRAFT      = 'draft';
	const STATUS_PENDING    = 'pending';
	const STATUS_PUBLISH    = 'publish';
	const STATUS_FUTURE     = 'future';
	const STATUS_AUTO_DRAFT = 'auto-draft';
	const STATUS_PRIVATE    = 'private';
	const STATUS_INHERIT    = 'inherit';

	const SORT_ASC  = 'asc';
	const SORT_DESC = 'desc';

	const ORDERBY_NONE         = 'none';
	const ORDERBY_ID           = 'ID';
	const ORDERBY_AUTHOR       = 'author';
	const ORDERBY_TITLE        = 'title';
	const ORDERBY_SLUG         = 'name';
	const ORDERBY_TYPE         = 'type';
	const ORDERBY_DATE         = 'date';
	const ORDERBY_MODIFIED     = 'modified';
	const ORDERBY_PARENT       = 'parent';
	const ORDERBY_RAND         = 'rand';
	const ORDERBY_COMMENTS     = 'comment_count';
	const ORDERBY_WEIGHT       = 'menu_order';
	const ORDERBY_META         = 'meta_value';
	const ORDERBY_META_NUMERIC = 'meta_value_num';
	const ORDERBY_POST_IN      = 'post__in';

	const SUPPORTS_TITLE          = 'title';
	const SUPPORTS_EDITOR         = 'editor';
	const SUPPORTS_GUTENBERG      = self::SUPPORTS_EDITOR;
	const SUPPORTS_AUTHOR         = 'author';
	const SUPPORTS_FEATURED_IMAGE = 'thumbnail';
	const SUPPORTS_EXCERPT        = 'excerpt';
	const SUPPORTS_TRACKBACKS     = 'trackbacks';
	const SUPPORTS_CUSTOM_FIELDS  = 'custom-fields';
	const SUPPORTS_COMMENTS       = 'comments';  // (also will see comment count balloon on edit screen)
	const SUPPORTS_REVISIONS      = 'revisions';
	const SUPPORTS_ORDER          = 'page-attributes'; // (menu order, hierarchical must be true to show Parent option)
	const SUPPORTS_POST_FORMATS   = 'post-formats';

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
	 * Has single page or not
	 * affect: show_in_nav_menus, publicly_queryable
	 *
	 * @var boolean
	 */
	protected $has_single = false;

	/**
	 * Should appear in search results or not
	 * affect: exclude_from_search, publicly_queryable
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

	/**
	 * Ability to set Parent
	 * affect: hierarchical
	 *
	 * @var boolean
	 */
	protected $is_hierarchical = false;

	/**
	 * Ability to display the CPT in the REST API
	 * Please be aware that this enable the CPT to use Gutenberg if supporting editor in WP 5.0 and up
	 * affect: show_in_rest
	 *
	 * @var boolean
	 */
	protected $has_rest_api = false;

	/**
	 * Specify where to redirect singular post type page
	 * IF has_singular == false AND is_searchable == true THEN we need to redirect page from search results to some other page
	 * custom property: auto-redirect to some URL
	 *
	 * @var boolean|string
	 */
	protected $redirect = false;

	/**
	 * Simplify URLs structure and remove "slug" prefix from URL
	 * affect: rewrite.with_front
	 *
	 * @var boolean
	 */
	protected $rewrite_singular = false;

	/**
	 * Show in admin menu
	 * affect: show_in_menu, show_ui
	 *
	 * @var boolean
	 */
	protected $has_admin_menu = true;

	/**
	 * Admin menu vertical position
	 * Be very careful and do not add more than 5 Post Types to same number!
	 * affect: menu_position
	 *
	 * @var integer
	 */
	protected $admin_menu_pos = 25;

	/**
	 * Admin menu icon
	 * https://developer.wordpress.org/resource/dashicons/
	 * affect: menu_icon
	 *
	 * @var string
	 */
	protected $admin_menu_icon = null;

	/**
	 * Array of supported standard features of CPT
	 * https://codex.wordpress.org/Function_Reference/register_post_type#supports
	 * affect: supports
	 *
	 * @var array
	 */
	protected $supports = array(
		self::SUPPORTS_TITLE,
		self::SUPPORTS_EDITOR,
		self::SUPPORTS_FEATURED_IMAGE,
		self::SUPPORTS_REVISIONS,
	);

	/**
	 * Optional properties to make full compatibility with standard register function.
	 */

	/**
	 * Taxonomy IDs to add to this CPT, will be used only for standard Category and Tags.
	 * All other custom taxonomies should register CPT they are used inside the Taxonomy
	 * affect: taxonomies
	 *
	 * @var null|array
	 */
	protected $taxonomies = null;

	/**
	 * Enable global archive page for CPT. We don't use it actually
	 * affect: has_archive
	 *
	 * @var boolean
	 */
	protected $has_archive = false;

	/**
	 * Labels array to override helper auto-generation
	 * https://codex.wordpress.org/Function_Reference/register_post_type#labels
	 * affect: labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Main capability level
	 * affect: capability_type
	 *
	 * @var string
	 */
	protected $admin_capability = 'post';

	/**
	 * Custom capability options
	 * https://codex.wordpress.org/Function_Reference/register_post_type#capabilities
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
	 * Textdomain to apply to labels on post type registration
	 *
	 * @var string
	 */
	protected $textdomain = 'justcoded_theme_framework';

	/**
	 * Constructor
	 * Check required parameter ID and add WP action hook
	 *
	 * @throws \Exception Missing $ID property.
	 */
	protected function __construct() {
		if ( empty( $this::$ID ) ) {
			throw new \Exception( 'Post Type "' . get_class( $this ) . '" init failed: missing $ID property' );
		}

		add_action( 'init', array( $this, 'init' ) );
		if ( method_exists( $this, 'faker' ) && is_admin() && FakerPress::check_requirements() ) {
			add_action( 'init', array( $this, 'init_faker' ) );
		}
		add_filter( 'template_include', array( $this, 'views' ), 10 );
	}

	/**
	 * Declaration of the 'init' action hook
	 * should call $this->register( params ) inside
	 */
	abstract public function init();

	/**
	 * Main function to register post type.
	 * Convert class properties to array of args for register_post_type
	 */
	public function register() {
		$labels = array(
			'name'          => _x( $this->label_multiple, 'post type general name', $this->textdomain ),
			'singular_name' => _x( $this->label_singular, 'post type singular name', $this->textdomain ),
			'menu_name'     => _x( $this->label_multiple, 'admin menu', $this->textdomain ),
			'add_new'       => _x( 'Add New', $this->label_singular, $this->textdomain ),
		);
		$labels = array_merge( $labels, $this->labels );

		// If we should support gutenberg, we need to enable the rest api.
		if ( self::SUPPORTS_GUTENBERG === 'editor' && ! $this->has_rest_api ) {
			$this->has_rest_api = true;
		}

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => $this->is_hierarchical,
			'public'              => $this->has_single,
			'publicly_queryable'  => $this->has_single || $this->is_searchable || $this->redirect,
			'show_in_nav_menus'   => $this->has_single,
			'exclude_from_search' => ! $this->is_searchable,
			'has_archive'         => $this->has_archive,
			'show_ui'             => ! empty( $this->has_admin_menu ),
			'show_in_menu'        => $this->has_admin_menu,
			'menu_position'       => $this->admin_menu_pos,
			'menu_icon'           => $this->admin_menu_icon,
			'capability_type'     => $this->admin_capability,
			'supports'            => $this->supports,
			'rewrite'             => array(
				'slug'       => $this::$SLUG,
				'with_front' => ( ! $this->rewrite_singular || $this->redirect ),
			),
			'query_var'           => $this->query_var,
			'show_in_rest'        => $this->has_rest_api,
		);

		if ( ! empty( $this->taxonomies ) && is_array( $this->taxonomies ) ) {
			$args['taxonomies'] = $this->taxonomies;
		}

		if ( ! empty( $this->capabilities ) ) {
			$args['capabilities'] = $this->capabilities;
		}

		// WP standard function call with combined params.
		register_post_type( $this::$ID, $args );

		// Check redirect parameter and register custom template redirect if needed.
		if ( $this->redirect ) {
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}
	}

	/**
	 * Perform redirect if single is blocked and redirect is configured
	 */
	public function template_redirect() {
		if ( is_singular( $this::$ID ) ) {
			wp_safe_redirect( $this->redirect, 301 );
			exit;
		}
	}

	/**
	 * Apply custom rules to template loader.
	 * By default point all inner CPT pages to views/{cpt}/single.php
	 * You can write your own rules here
	 *
	 * @param string $template Standard template file to be loaded.
	 *
	 * @return string Modified template file path
	 */
	public function views( $template ) {
		/**
		 * Example of overwrite:
		 *  if ( is_singular( $this::$ID ) ) {
		 *     $template = View::locate('section/template');
		 *    }
		 */

		return $template;
	}

	/**
	 * Init FakerPress.
	 */
	public function init_faker() {
		new FakerPress( $this );
	}
}
