<?php

namespace JustCoded\WP\Framework;

use JustCoded\WP\Framework\Objects\Singleton;
use JustCoded\WP\Framework\Supports\Autoptimize;
use JustCoded\WP\Framework\Supports\Just_Custom_Fields;
use JustCoded\WP\Framework\Supports\Just_Post_Preview;
use JustCoded\WP\Framework\Supports\Just_Responsive_Images;
use JustCoded\WP\Framework\Supports\Just_Tinymce;
use JustCoded\WP\Framework\Web\Template_Hierarchy;
use JustCoded\WP\Framework\Supports\Just_Load_More;
use JustCoded\WP\Framework\Web\View;

/**
 * Main base class for theme.
 * Init main path rewrite operations and activate / register hooks
 */
abstract class Theme {
	use Singleton;

	/**
	 * Theme version
	 *
	 * @var float
	 */
	public $version = 1.0;

	/**
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 *
	 * @var boolean $auto_title
	 */
	public $auto_title = true;

	/**
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @var boolean
	 */
	public $post_thumbnails = true;

	/**
	 * Add allowed mime types for upload.
	 * Will be added to a standard WordPress list.
	 *
	 * If you need SVG uploads, you should also add the line below to your `wp-config.php` file:
	 *      define( 'ALLOW_UNFILTERED_UPLOADS', true )
	 *
	 * @var array
	 */
	public $upload_mime_types = array(
		'svg' => 'image/svg+xml',
	);

	/**
	 * Available image sizes in Media upload dialog to insert correctly resized image.
	 *
	 * @var array
	 */
	public $available_image_sizes = array(
		'thumbnail' => 'Thumbnail',
		'medium'    => 'Medium',
		'large'     => 'Large',
		'full'      => 'Full Size',
	);

	/**
	 * JPEG images compression quality value
	 * max 100%
	 *
	 * @var int
	 */
	public $jpeg_quality = 100;

	/**
	 * Enable support for Post Formats.
	 * Set FALSE to disable post formats
	 *
	 * @var array $post_formats
	 * @link https://developer.wordpress.org/themes/functionality/post-formats/
	 */
	public $post_formats = array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
	);

	/**
	 * Enable/disable admin bar
	 *
	 * @var boolean $show_admin_bar
	 */
	public $show_admin_bar = true;

	/**
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 * Set FALSE to skip
	 *
	 * @var array $html5
	 */
	public $html5 = array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	);

	/**
	 * Disable gutenberg for posts and custom post type.
	 *
	 * Set TRUE to disable it totally.
	 * Set ARRAY to disable only specific ones.
	 *
	 * @var array|bool $disable_gutenberg
	 */
	public $disable_gutenberg;

	/**
	 * Name of frontend assets folder.
	 *
	 * @var string $assets_folder_name
	 */
	public $assets_folder_name = 'assets';

	/**
	 * Init actions and hooks
	 */
	protected function __construct() {
		$this->register_post_types();
		$this->register_taxonomies();
		$this->init_views_templates();
		$this->register_api_endpoints();

		/**
		 * Pretty standard theme hooks
		 */
		add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		add_filter( 'jpeg_quality', array( $this, 'filter_jpeg_quality' ) );

		// activate hooks to clean up rewrite rules.
		add_action( 'after_switch_theme', array( $this, 'activate' ) );
		add_action( 'switch_theme', array( $this, 'deactivate' ) );

		add_filter( 'image_size_names_choose', array( $this, 'image_size_names_choose' ) );

		/**
		 * Remove Unnecessary Code from wp_head
		 */
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'start_post_rel_link' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link' );
		add_action( 'wp_enqueue_scripts', array( $this, 'remove_base_stylesheet' ), 20 );
		if ( ! is_admin() ) {
			add_filter( 'script_loader_src', array( $this, 'remove_assets_query_string' ), 15, 1 );
			add_filter( 'style_loader_src', array( $this, 'remove_assets_query_string' ), 15, 1 );
		}

		$this->support_plugins();
		$this->init();
	}

	/**
	 * Remove default stylesheet
	 */
	public function remove_base_stylesheet() {
		/* @var \WP_Theme $theme */
		$theme      = wp_get_theme();
		$stylesheet = $theme->get_stylesheet();

		wp_dequeue_style( "$stylesheet-style" );
		wp_deregister_style( "$stylesheet-style" );
	}

	/**
	 * Remove query string from static resources
	 *
	 * @param string $src Assets src path.
	 *
	 * @return string
	 */
	public function remove_assets_query_string( $src ) {
		if ( strpos( $src, '?ver' ) !== false ) {
			$src_parts = explode( '?ver', $src, 2 );
			$src       = array_shift( $src_parts );
		}
		if ( strpos( $src, '&ver' ) !== false ) {
			$src_parts = explode( '&ver', $src, 2 );
			$src       = array_shift( $src_parts );
		}

		return $src;
	}

	/**
	 * Init new Template Hierarchy based on "views" folder and load Views engine.
	 */
	public function init_views_templates() {
		Template_Hierarchy::instance();
		View::instance();
	}

	/**
	 * Called right away after constructor. You can define/call additional actions here
	 */
	public function init() {
	}

	/**
	 * Theme activation hook.
	 * Flush rewrite rules on activate
	 *
	 * @param string         $oldname previous active theme name.
	 * @param string|boolean $oldtheme previous active theme.
	 */
	public function activate( $oldname, $oldtheme = false ) {
		flush_rewrite_rules();
	}

	/**
	 * Theme deactivation hook.
	 * Flush rewrite rules on activate
	 *
	 * @param string         $newname new active theme name.
	 * @param string|boolean $newtheme new active theme.
	 */
	public function deactivate( $newname, $newtheme = '' ) {
		// do nothing.
	}

	/**
	 * Main theme setup function.
	 *
	 * Register components, theme options, widgets etc
	 *
	 * Should be called on after_theme_setup action hook
	 */
	public function theme_setup() {
		if ( $this->auto_title ) {
			add_theme_support( 'title-tag' );
		}

		if ( $this->post_thumbnails ) {
			add_theme_support( 'post-thumbnails' );
		}

		if ( ! empty( $this->post_formats ) && is_array( $this->post_formats ) ) {
			add_theme_support( 'post-formats', $this->post_formats );
		}

		if ( ! $this->show_admin_bar ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}

		if ( $this->upload_mime_types ) {
			add_filter( 'upload_mimes', array( $this, 'add_upload_mimes' ) );
		}

		if ( ! empty( $this->html5 ) && is_array( $this->html5 ) ) {
			add_theme_support( 'html5', $this->html5 );
		}

		if ( isset( $this->disable_gutenberg ) ) {
			if ( is_bool( $this->disable_gutenberg ) && ! empty( $this->disable_gutenberg ) ) {
				add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );
			}

			if ( is_array( $this->disable_gutenberg ) ) {
				add_filter( 'use_block_editor_for_post_type', function ( $use_block_editor, $post_type ) {
					return ! in_array( $post_type, $this->disable_gutenberg, true );
				}, 10, 2 );
			}
		}

		/**
		 * Remove global content width.
		 * This can affect "width" attribute for images. If required can be overwritten in app file.
		 *
		 * @global int $GLOBALS ['content_width']
		 * @name       $content_width
		 * @link https://codex.wordpress.org/Content_Width
		 */
		$GLOBALS['content_width'] = '';
	}

	/**
	 * Overwrite available image sizes inside media upload screen.
	 *
	 * @param array $sizes WordPress standard sizes array.
	 *
	 * @return array
	 */
	public function image_size_names_choose( $sizes ) {
		return $this->available_image_sizes;
	}

	/**
	 * Register styles and scripts
	 *
	 * Called on 'wp_enqueue_scripts'
	 */
	abstract public function register_assets();

	/**
	 * Register array of css files one-by-one.
	 * You can set general dependencies for all of them
	 *
	 * @param array       $styles Array of css files to register.
	 * @param array       $dependencies Array of script files which should be registered before this.
	 * @param null|string $base_uri Uri prefix for all scripts from.
	 *
	 * @return void|null
	 */
	public function register_assets_css( $styles, $dependencies = array(), $base_uri = null ) {
		if ( empty( $styles ) ) {
			return;
		}
		if ( ! is_array( $styles ) ) {
			$styles = array( $styles );
		}
		if ( is_null( $base_uri ) ) {
			$base_uri = get_template_directory_uri() . '/' . $this->assets_folder_name . '/css/';
		}

		foreach ( $styles as $filename ) {
			wp_enqueue_style( '_jmvt-' . preg_replace( '/(\.(.+?))$/', '', $filename ), $base_uri . $filename, $dependencies );
		}
	}

	/**
	 * Register array of script files one-by-one.
	 * You can set general dependencies for all of them
	 *
	 * @param array       $scripts Array of script files to register.
	 * @param array       $dependencies Array of script files which should be registered before this.
	 * @param null|string $base_uri Uri prefix for all scripts from.
	 *
	 * @return void|null
	 */
	public function register_assets_scripts( $scripts, $dependencies = array(), $base_uri = null ) {
		if ( empty( $scripts ) ) {
			return;
		}
		if ( ! is_array( $scripts ) ) {
			$scripts = array( $scripts );
		}

		if ( is_null( $base_uri ) ) {
			$base_uri = get_template_directory_uri() . '/' . $this->assets_folder_name . '/js/';
		}

		foreach ( $scripts as $filename ) {
			wp_enqueue_script( '_jmvt-' . preg_replace( '/(\.(.+?))$/', '', $filename ), $base_uri . $filename, $dependencies, $this->version, true );
		}
	}

	/**
	 * Register theme sidebars
	 * Usage:
	 *     register_sidebar( ... );
	 *
	 * Called on 'widgets_init'
	 */
	public function register_sidebar() {
	}

	/**
	 * Register custom widgets
	 * Usage:
	 *     include_once 'widgets/MyWidget.php';
	 *     register_widget( 'MyWidget');
	 *
	 * Called on 'widgets_init'
	 */
	public function register_widgets() {
	}

	/**
	 * Register post types
	 * Usage:
	 *      new \namespace\App\Post_Type\MyPostType();
	 *
	 * Each post type register it's own action hook
	 */
	public function register_post_types() {
	}

	/**
	 * Register taxonomies
	 * Usage:
	 *      new \namespace\App\Taxonomy\MyTaxonomy();
	 *
	 * Each Taxonomy register it's own action hook
	 */
	public function register_taxonomies() {
	}

	/**
	 * Register API Endpoints
	 * Usage:
	 *      new \namespace\App\Endpoints\MyEndpoint();
	 *
	 * Each endpoint register it's own action hook
	 */
	public function register_api_endpoints() {

	}

	/**
	 * Adds loading of custom features provided by 3d-party plugins.
	 */
	public function support_plugins() {
		Just_Load_More::instance();
		Just_Responsive_Images::instance();
		Just_Custom_Fields::instance();
		Just_Post_Preview::instance();
		Just_Tinymce::instance();
	}

	/**
	 * Filter JPEG image quality compression to prevent image quality loss
	 *
	 * @return int
	 */
	public function filter_jpeg_quality() {
		// limit jpeg_quality between 10 and 100%.
		$quality = max( 10, min( $this->jpeg_quality, 100 ) );

		return $quality;
	}

	/**
	 * Filters list of allowed mime types and file extensions.
	 * By default we add new extensions, which are defined in $this->upload_mime_types property
	 *
	 * @param array $mimes Mime types keyed by the file extension regex corresponding to
	 *                        those types. 'swf' and 'exe' removed from full list. 'htm|html' also
	 *                        removed depending on '$user' capabilities.
	 *
	 * @return array
	 */
	public function add_upload_mimes( $mimes ) {
		$mimes = array_merge( $mimes, $this->upload_mime_types );

		return $mimes;
	}

}
