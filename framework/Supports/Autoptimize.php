<?php

namespace JustCoded\WP\Framework\Supports;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class Autoptimize
 * Autoptimize plugin extension which allows to add advanced configuration of this plugin.
 * Also add patches for multiSite and "/cms" folder installation
 */
class Autoptimize {
	use Singleton;

	/**
	 * Domains to be added as <link prefetch>
	 *
	 * @var array
	 */
	protected $dns_prefetch_domains = array();

	/**
	 * Cached string of current blog url
	 * MultiSite only
	 *
	 * @var string
	 */
	protected $current_blog_site_url;

	/**
	 * Cached string of main blog url
	 * Multisite only
	 *
	 * @var string
	 */
	protected $main_blog_site_url;

	/**
	 * AutoOptimize constructor.
	 */
	protected function __construct() {
		// autoptimize patches.
		add_filter( 'autoptimize_filter_js_unmovable', array( $this, 'js_is_unmovable' ) );
		add_filter( 'autoptimize_filter_js_domove', array( $this, 'js_move_first' ) );
		add_filter( 'autoptimize_filter_js_movelast', array( $this, 'js_move_last' ) );
		add_filter( 'autoptimize_filter_js_dontmove', array( $this, 'js_dontmove' ) );
		add_filter( 'autoptimize_filter_js_exclude', array( $this, 'js_exclude' ) );

		if ( MULTISITE ) {
			$this->remember_main_blog_vars();

			$blog_id = get_current_blog_id();
			if ( $blog_id > 1 ) {
				add_filter( 'autoptimize_filter_cssjs_alter_url', array( $this, 'assets_alter_url_patch' ) );
			}
		}
		add_filter( 'autoptimize_filter_html_before_minify', array( $this, 'add_dns_prefetch' ) );
		add_filter( 'autoptimize_filter_html_before_minify', array( $this, 'add_external_links_target_rel' ) );
	}

	/**
	 * Check that required plugin is installed and activated
	 *
	 * @return bool
	 */
	public static function check_requirements() {
		return is_plugin_active( 'autoptimize/autoptimize.php' );
	}

	/**
	 * Set cache for urls vars
	 * Used inside autoOptimize plugin filters
	 */
	public function remember_main_blog_vars() {
		switch_to_blog( 1 );
		$this->main_blog_site_url = site_url();

		restore_current_blog();
		$this->current_blog_site_url = site_url();
	}

	/**
	 * Generate dns prefetch block for the content
	 *
	 * @param string $content HTML content generated for the page.
	 *
	 * @return string
	 */
	public function add_dns_prefetch( $content ) {
		$prefetch_domains = array();

		// find external scripts in LINK, SCRIPT, IMG tags.
		$tags_matches = array();
		if ( preg_match_all( '#<link.*>#Usmi', $content, $matches ) ) {
			$tags_matches = array_merge( $tags_matches, $matches[0] );
		}
		if ( preg_match_all( '#<script.*</script>#Usmi', $content, $matches ) ) {
			$tags_matches = array_merge( $tags_matches, $matches[0] );
		}
		if ( preg_match_all( '#<img.*>#Usmi', $content, $matches ) ) {
			$tags_matches = array_merge( $tags_matches, $matches[0] );
		}

		if ( empty( $tags_matches ) ) {
			return $content;
		}

		foreach ( $tags_matches as $tag ) {
			if ( preg_match( '#(http\:\/\/|https\:\/\/|\/\/)(([a-z0-9\_\-\.]+)\.([a-z0-9]{2,5}))\/#Usmi', $tag, $domain ) ) {
				$prefetch_domains[] = $domain[0];
			}
		}

		preg_match( '#http(s)?\:\/\/(([a-z0-9\_\-\.]+)\.([a-z0-9]{2,5}))\/?#', site_url(), $site_domain );
		$http_domain  = "http://{$site_domain[2]}/";
		$https_domain = "https://{$site_domain[2]}/";

		$prefetch_domains = array_merge( $prefetch_domains, $this->dns_prefetch_domains );
		$prefetch_domains = array_diff( $prefetch_domains, array( $http_domain, $https_domain ) );
		$prefetch_domains = array_unique( $prefetch_domains );

		$prefetch_meta = array( '' );
		foreach ( $prefetch_domains as $domain ) {
			$domain          = str_replace( 'http://', '//', rtrim( $domain, '/' ) );
			$prefetch_meta[] = "<link rel=\"dns-prefetch\" href=\"$domain\" />";
		}

		$content = str_replace( '<head>', '<head>' . implode( "\n", $prefetch_meta ), $content );

		return $content;
	}

	/**
	 * Add target and rel for links.
	 *
	 * @param string $content HTML content generated for the page.
	 *
	 * @return string
	 */
	public function add_external_links_target_rel( $content ) {
		if ( ! preg_match_all( '#<a.*>.*<\/a>#Usmi', $content, $matches ) ) {
			return $content;
		}

		preg_match( '#http(s)?\:\/\/(([a-z0-9\_\-\.]+)\.([a-z0-9]{2,5}))\/?#', site_url(), $site_domain );

		foreach ( $matches[0] as $tag ) {
			if ( preg_match( '#href="(http\:\/\/|https\:\/\/|\/\/)(([a-z0-9\_\-\.]+)\.([a-z0-9]{2,5}))(\/|\")#Usmi', $tag, $domain ) ) {
				if ( false !== strpos( $domain[2], $site_domain[2] ) ) {
					continue;
				}

				$basic_tag = $tag;

				if ( ! preg_match( '#rel="(.*)"#Usmi', $basic_tag, $rel_domain ) ) {
					$tag = str_replace( $domain[0], 'rel="noopener noreferrer" ' . $domain[0], $tag );
				}

				if ( ! preg_match( '#target="(.*)"#Usmi', $basic_tag, $target_domain ) ) {
					$tag = str_replace( $domain[0], 'target="_blank" ' . $domain[0], $tag );
				}

				$content = str_replace( $basic_tag, $tag, $content );
			}
		}

		return $content;
	}

	/**
	 * Patch autoOptimize function to get correct script URLs.
	 * For MultiSite we have another folder with "cms" that in real URL
	 *
	 * @param string $url URL to be replaced with real URL on MultiSites.
	 *
	 * @return mixed
	 */
	public function assets_alter_url_patch( $url ) {
		if ( strpos( $url, 'wp-includes/' ) === false && strpos( $url, 'wp-admin/' ) === false ) {
			return $url;
		}

		return str_replace( $this->current_blog_site_url, $this->main_blog_site_url, $url );
	}

	/**
	 * Enable back feature to move all scripts to bottom
	 *
	 * @param boolean $value Can scripts be moved between each other. Return false to allow movements.
	 *
	 * @return boolean
	 */
	public function js_is_unmovable( $value ) {
		return false;
	}

	/**
	 * Add scripts to be moved BEFORE autoOptimized minified script loaded
	 *
	 * @param array $scripts scripts to be moved first.
	 *
	 * @return array
	 */
	public function js_move_first( $scripts ) {
		$scripts[] = '/jquery.js';
		$scripts[] = '/jquery.min.js';
		$scripts[] = 'var _wpcf7';
		return $scripts;
	}

	/**
	 * Add scripts to be moved AFTER autoOptimized minified script loaded
	 *
	 * @param array $scripts scripts to be moved last.
	 *
	 * @return array
	 */
	public function js_move_last( $scripts ) {
		// Wordfence plugin scripts.
		$scripts[] = 'hid';
		$scripts[] = 'wordfence_logHuman';
		return $scripts;
	}

	/**
	 * Add scripts which should not be moved at all.
	 *
	 * @param array $scripts Scripts to leave as is.
	 *
	 * @return array
	 */
	public function js_dontmove( $scripts ) {
		return $scripts;
	}

	/**
	 * Exclude scripts from optimization.
	 *
	 * @param string $exclude_js Comma separated list to be excluded from processing.
	 *
	 * @return string
	 */
	public function js_exclude( $exclude_js ) {
		return $exclude_js;
	}

}
