<?php

namespace JustCoded\WP\Framework\Web;

use JustCoded\WP\Framework\Objects\Singleton;
use JustCoded\WP\Framework\Web\View;

/**
 * Class Template_Hierarchy
 * Hook get_*_template() functions to add "views" folder everywhere
 */
class Template_Hierarchy {
	use Singleton;

	/**
	 * Standard template types available for rewrite.
	 *
	 * @var array
	 */
	protected $template_types = array(
		'index',
		'404',
		'archive',
		'author',
		'category',
		'tag',
		'taxonomy',
		'date',
		'embed',
		'home',
		'frontpage',
		'page',
		'paged',
		'search',
		'single',
		'singular',
		'attachment',
	);

	/**
	 * Declaration of sections for standard template types.
	 *
	 * @var array
	 */
	protected $template_types_folders = array(
		'views/page'   => array( '404', 'frontpage', 'page', 'paged' ),
		'views/post'   => array( 'home', 'index', 'archive', 'author', 'category', 'tag', 'date', 'single' ),
		'views/search' => array( 'search' ),
		'views'        => array( 'embed', 'attachment', 'singular', 'taxonomy' ),
	);

	/**
	 * Template_Hierarchy constructor.
	 * set WordPress template system hooks
	 */
	protected function __construct() {
		// patch page/custom post type templates.
		add_action( 'init', array( $this, 'init_theme_page_template_hooks' ), 1000 );

		// set filter for all query template types.
		foreach ( $this->template_types as $type ) {
			add_filter( "{$type}_template_hierarchy", array( $this, "{$type}_template_hierarchy" ) );
		}

		// add woocommerce support.
		add_filter( 'woocommerce_template_path', array( $this, 'woocommerce_template_path' ) );

		// Support of ACF, to display page templates from subfolders.
		add_filter( 'acf/location/rule_values/page_template', array( $this, 'acf_page_templates_scan' ), 20, 1 );
	}

	/**
	 * Add hooks for all post types for check post_templates in views folders as well
	 */
	public function init_theme_page_template_hooks() {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			add_filter( "theme_{$post_type}_templates", array( $this, 'theme_page_templates' ), 20, 4 );
		}
	}

	/**
	 * Forward all template hierarchy hooks to 1 method with specific "type" variable
	 *
	 * @param string $name unknown method name.
	 * @param array  $arguments method arguments.
	 *
	 * @return mixed
	 * @throws \Exception Method does not exists.
	 */
	public function __call( $name, $arguments ) {
		if ( ! preg_match( '/^([a-z0-9\_]+)_template_hierarchy$/i', $name, $matches ) ) {
			throw new \Exception( 'Method does not exists ' . get_class( $this ) . "::{$name}()" );
		}

		$type = $matches[1];

		return $this->extend_template_hierarchy( $type, $arguments[0] );
	}

	/**
	 * General entry point for template rewrites system
	 *
	 * @param string $type page query type.
	 * @param array  $templates standard WP templates.
	 *
	 * @return array
	 */
	protected function extend_template_hierarchy( $type, $templates ) {
		global $_jtf_templates_hierarchy_lookup;

		// generate template for post types.
		if ( 'single' === $type && $templates = $this->get_single_templates() ) :
		elseif ( 'page' === $type && $templates = $this->get_page_templates( $templates ) ) :
		elseif ( 'archive' === $type && $templates = $this->get_archive_templates() ) :
			// defaults - wrap with views folders based on type.
		elseif ( $templates = $this->wrap_template_in_folders( $type, $templates ) ) :
		endif;

		return $templates;
	}

	/**
	 * Generate views for custom page templates set from admin.
	 * It search for page template set from admin. If it was deleted - then load generic page template
	 *
	 * @param array $std_templates Standard WordPress templates.
	 *
	 * @return array
	 */
	protected function get_page_templates( $std_templates ) {
		if ( $custom_view = get_page_template_slug() ) {
			$templates = array(
				$custom_view,
				'views/page/page.php',
			);
		} else {
			$templates = $this->wrap_template_in_folders( 'page', $std_templates );
		}

		return $templates;
	}

	/**
	 * Generate rules for loading single pages (including custom post types)
	 * Rules are:
	 *        {post_type}/single-{post name}.php
	 *        {post_type}/single.php
	 *
	 * @return array
	 */
	protected function get_single_templates() {
		$object    = get_queried_object();
		$templates = array();

		if ( ! empty( $object->post_type ) ) {
			$folder = "views/{$object->post_type}";

			if ( $custom_view = get_page_template_slug() ) {
				$templates[] = $custom_view;
			}

			$name_decoded = urldecode( $object->post_name );
			if ( $name_decoded !== $object->post_name ) {
				$templates[] = "$folder/single-{$name_decoded}.php";
			}

			$templates[] = "$folder/single-{$object->post_name}.php";
			$templates[] = "$folder/single.php";
		}

		return $templates;
	}

	/**
	 * Archive templates. By default they are off for custom post types.
	 * Rules are:
	 *        {post_type}/archive.php
	 *        {post_type}/index.php
	 *
	 * @return array
	 */
	protected function get_archive_templates() {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}
		if ( ! $post_type ) {
			$post_type = 'post';
		}

		$templates = array(
			"views/$post_type/archive.php",
			"views/$post_type/index.php",
		);

		return $templates;
	}

	/**
	 * Add "views/{post_type}/" folder for all templates, set by WP.
	 * This allows us to follow almost same WP template hierarchy rules
	 *
	 * @param string $type page query type.
	 * @param array  $templates standard WP templates.
	 *
	 * @return array
	 */
	protected function wrap_template_in_folders( $type, $templates ) {
		$foldered_templates = array();

		foreach ( $this->template_types_folders as $folder => $folder_types ) {
			if ( ! in_array( $type, $folder_types, true ) ) {
				continue;
			}

			foreach ( $templates as $template ) {
				if ( strpos( $template, 'views' ) !== 0 ) {
					$foldered_templates[] = "{$folder}/{$template}";
				}
			}
			break;
		}

		return $foldered_templates;
	}

	/**
	 * Hook to add post templates scanned in deeper level under /views folder
	 *
	 * @param array     $post_templates  Post templates found by WP.
	 * @param \WP_Theme $wp_theme        Current activated theme.
	 * @param \WP_Post  $post            Current post object.
	 * @param string    $post_type       Current post object type.
	 *
	 * @return array
	 */
	public function theme_page_templates( $post_templates, $wp_theme, $post, $post_type ) {
		$post_templates_depth2 = $this->scan_post_templates( 2 );
		$post_templates_depth2 = isset( $post_templates_depth2[ $post_type ] ) ? $post_templates_depth2[ $post_type ] : array();

		$post_templates = array_merge( $post_templates_depth2, $post_templates );

		return $post_templates;
	}

	/**
	 * Scan post templates, copied from wp core files
	 *
	 * @see class-wp-theme.php:1017
	 *
	 * @param integer $depth Maximum recursion depth.
	 *
	 * @return array
	 */
	public function scan_post_templates( $depth ) {
		/* @var \WP_Theme $wp_theme */
		$wp_theme = wp_get_theme();
		if ( $wp_theme->errors() && $wp_theme->errors()->get_error_codes() !== array( 'theme_parent_invalid' ) ) {
			return array();
		}

		$cache_hash = md5( 'JustCoded\WP\Framework-' . $wp_theme->get_stylesheet() );

		$post_templates = wp_cache_get( "post_templates-depth{$depth}-{$cache_hash}", 'themes' );

		if ( ! is_array( $post_templates ) ) {
			$post_templates = array();

			$files = (array) $wp_theme->get_files( 'php', $depth );
			// add support for child themes. they should load parent theme templates as well.
			if ( $wp_theme->parent() ) {
				$parent_files = $wp_theme->parent()->get_files( 'php', $depth );
				$files        = array_merge( $parent_files, $files );
			}

			foreach ( $files as $file => $full_path ) {
				if ( __FILE__ === $full_path ) {
					continue;
				}

				if ( ! preg_match( '|Template Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
					continue;
				}

				$types = array( 'page' );
				if ( preg_match( '|Template Post Type:(.*)$|mi', file_get_contents( $full_path ), $type ) ) {
					$types = explode( ',', _cleanup_header_comment( $type[1] ) );
				}

				foreach ( $types as $type ) {
					$type = sanitize_key( $type );
					if ( ! isset( $post_templates[ $type ] ) ) {
						$post_templates[ $type ] = array();
					}

					$post_templates[ $type ][ $file ] = _cleanup_header_comment( $header[1] );
				}
			}

			wp_cache_add( "post_templates-depth{$depth}-{$cache_hash}", $post_templates, 'themes', 1800 );
		}

		return $post_templates;
	}

	/**
	 * Patch woocommerce to search templates inside /views
	 *
	 * @param string $wocommerce_path Woocommerce template path.
	 *
	 * @return string
	 */
	public function woocommerce_template_path( $wocommerce_path ) {
		return 'views/' . $wocommerce_path;
	}

	/**
	 * Add to page templates templates inside the "views/{section}" folders
	 *
	 * @param array $page_templates ACF page templates patch.
	 *
	 * @return array mixed
	 */
	public function acf_page_templates_scan( $page_templates ) {
		$templates = wp_get_theme()->get_files( 'php', 2 );

		foreach ( (array) $templates as $file => $full_path ) {
			if ( __FILE__ === $full_path ) {
				continue;
			}

			if ( ! preg_match( '|Template\sName:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
				continue;
			}

			$page_templates[ $file ] = _cleanup_header_comment( $header[1] );
		}

		return $page_templates;
	}

}
