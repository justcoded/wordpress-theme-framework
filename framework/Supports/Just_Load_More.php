<?php
/**
 * Class Just_Load_More
 *
 * Patch the default ajax load more pagination.
 *
 * @package JustCoded\ThemeFramework\Supports
 */

namespace JustCoded\ThemeFramework\Supports;

/**
 * Class Just_Load_More
 *
 * @package JustCoded\ThemeFramework\Supports
 */
class Just_Load_More {
	/**
	 * Just_Load_More constructor.
	 *
	 * Register plugin hooks.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		if ( isset( $_POST['jtf_load_more'] ) && ! defined( 'JTF_LOAD_MORE_AJAX' ) ) {
			define( 'JTF_LOAD_MORE_AJAX', true );
			add_filter( 'autoptimize_filter_noptimize', [ $this, 'disable_autoptimize' ] );

			ob_start( [ $this, 'ob_get_clean' ] );
		}
	}

	/**
	 * disable autooptimaze if using load more
	 */
	public function disable_autoptimize() {
		return true;
	}

	/**
	 * registered scripts
	 */
	public function register_assets() {
		wp_enqueue_script( '_jtf-load_more', jtf_plugin_url( 'assets/js/load_more.js' ), [ 'jquery' ] );
	}

	/**
	 * callback for loadmore script
	 *
	 * @param $html buffer output
	 *
	 * @return string
	 */
	public function ob_get_clean( $html ) {
		$text = '';
		if ( isset( $_POST['jtf_selector'] ) ) {
			$selector  = trim( $_POST['jtf_selector'] );
			$attribute = substr( $selector, 0, 1 );
			if ( $attribute == '.' || $attribute == '#' ) {
				$selector = substr( $selector, 1 );
			}
			$attribute                = ( $attribute == '#' ) ? $attribute = 'id' : $attribute = 'class';
			$doc                      = new \DOMDocument();
			$doc->recover             = true;
			$doc->strictErrorChecking = false;

			@$doc->loadHTML( $html );
			$xpath = new \DOMXpath( $doc );
			$divs  = $xpath->query( '//*[@' . $attribute . '="' . $selector . '"]/*' );
			for ( $i = 0; $i < $divs->length; $i ++ ) {
				$text .= $doc->saveHTML( $divs->item( $i ) );
			}
		}

		return $text;
	}

}