<?php
namespace justtheme\App;

use JustCoded\ThemeFramework\Supports\ContactForm7;
use JustCoded\ThemeFramework\Supports\JustCustomFields;
use JustCoded\ThemeFramework\Supports\JustPostPreview;
use JustCoded\ThemeFramework\Supports\JustResponsiveImages;
use JustCoded\ThemeFramework\Supports\JustTinymce;
use justtheme\App\PostType;

/**
 * Theme main entry point
 *
 * Theme setup functions, assets, post types, taxonomies declarations
 */
class Theme extends \JustCoded\ThemeFramework\Theme {

	/**
	 * Enable support for Post Formats.
	 *
	 * Set FALSE to disable post formats
	 *
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 *
	 * @var array $post_formats
	 */
	public $post_formats = array(
		'image',
		'video',
	);

	/**
	 * Main theme setup function.
	 *
	 * Register components, theme options, widgets etc
	 *
	 * Should be called on after_theme_setup action hook
	 */
	public function theme_setup() {
		parent::theme_setup();

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'justtheme' ),
		) );
	}

	/**
	 * Register theme sidebars
	 *
	 * Called on 'widgets_init'
	 */
	public function register_sidebars() {
		register_sidebar( array(
			'name'          => esc_html__( 'Sidebar', 'justtheme' ),
			'id'            => 'sidebar-1',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}

	/**
	 * Register styles and scripts
	 *
	 * Called on 'wp_enqueue_scripts'
	 */
	public function register_assets() {
		// Stylesheets.
		$this->register_assets_css( array(
			'styles.css',
		) );

		// Scripts.
		$this->register_assets_scripts( array(
			'jquery.main.js'
		), array( 'jquery' ) );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Register post types
	 */
	public function register_post_types() {
		new PostType\Hero();
	}

	/**
	 * Loads hooks for 3d-party plugins.
	 */
	public function support_plugins() {
		new JustResponsiveImages();
		new JustCustomFields();
		new JustTinymce();

		if ( ContactForm7::check_requirements() ) {
			new ContactForm7();
		}
	}
}