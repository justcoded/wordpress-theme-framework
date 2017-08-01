<?php
/**
 * Class JustLoadMore
 *
 * Patch the default ajax load more pagination.
 *
 * @package JustCoded\ThemeFramework\Supports
 */

namespace JustCoded\ThemeFramework\Supports;

class JustLoadMore {
	/**
	 * JustLoadMore constructor.
	 *
	 * Register plugin hooks.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		if ( isset( $_POST['jtf_load_more'] ) ) {
			if ( ! defined( 'JTF_LOAD_MORE_AJAX' ) ) {
				define( 'JTF_LOAD_MORE_AJAX', true );
				ob_start( [ $this, 'load_more_callback' ] );
			}
		}
	}

	/**
	 * registered scripts
	 */
	public function register_assets() {
		wp_enqueue_script( '_jmvt-load_more', jtf_plugin_url( 'assets/js/load_more.js' ), [ 'jquery' ] );
	}

	/**
	 * callback for loadmore script
	 *
	 * @param $html buffer output
	 *
	 * @return string
	 */
	public function load_more_callback( $html ) {
		$text = '';
		if ( isset( $_POST['jtf-selector'] ) ) {
			$selector  = trim( $_POST['jtf-selector'] );
			$container = isset( $_POST['jtf-container'] ) ? trim( $_POST['jtf-container'] ) : 'div';
			$attribute = isset( $_POST['jtf-attribute'] ) ? trim( $_POST['jtf-attribute'] ) : 'class';;
			$doc                      = new \DOMDocument();
			$doc->recover             = true;
			$doc->strictErrorChecking = false;

			@$doc->loadHTML( $html );
			$xpath = new \DOMXpath( $doc );
			$divs  = $xpath->query( '//' . $container . '[@' . $attribute . '="' . $selector . '"]/*' );
			$count = $xpath->query( '//' . $container . '[@' . $attribute . '="' . $selector . '"]/*' )->length;
			for ( $i = 0; $i < $count; $i ++ ) {
				$text .= $doc->saveHTML( $divs->item( $i ) );
			}
		}

		return $text;
	}

}