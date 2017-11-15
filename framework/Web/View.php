<?php
namespace Just_Coded\Theme_Framework\Web;

/**
 * Views base class.
 * Used for layouts and render partials
 */
class View {
	/**
	 * Current layout name
	 *
	 * @var string
	 */
	private static $_layout;

	/**
	 * Layouts call chain
	 *
	 * @var array
	 */
	private static $_processing = array();

	/**
	 * Start recording the content to be passed to the layout template
	 *
	 * @param string $layout Layout name to be rendered after views render.
	 */
	public static function layout_open( $layout = 'main' ) {
		// WordPress compatibility to still process usual headers.
		do_action( 'get_header', null );

		// check that we have required template.
		$template = static::locate( 'layouts/' . $layout, true );

		// memorize the template.
		static::$_layout = $layout;
		array_push( static::$_processing, $template );

		// start buffer.
		ob_start();
		ob_implicit_flush( false );
	}

	/**
	 * Stop recording content part and final render of the layout
	 *
	 * @throws \Exception No layout_open were called before close.
	 */
	public static function layout_close() {
		if ( empty( static::$_processing ) ) {
			throw new \Exception( 'Unexpected Views::layout_close() method call. Check that you have layout_open() call and not close layout before.' );
		}

		// get content.
		$content = ob_get_clean();

		// reset query to protect header from unclosed query in the content.
		wp_reset_postdata();

		// render under the existing context.
		include array_pop( static::$_processing );
	}

	/**
	 * WordPress compatibility option instead of get_sidebar, to run a sidebar hook
	 *
	 * @param string|null $name custom sidebar name.
	 */
	public static function sidebar_begin( $name = null ) {
		// WordPress compatibility.
		do_action( 'get_footer', $name );
	}

	/**
	 * WordPress compatibility option intead of get_sidebar
	 *
	 * @param string|name $name custom footer name.
	 */
	public static function footer_begin( $name = null ) {
		// WordPress compatibility.
		do_action( 'get_footer', $name );
	}

	/**
	 * Include some views template with specified params
	 *
	 * @param string  $view template name.
	 * @param array   $params params to be passed to the view.
	 * @param boolean $__required print message if views not found.
	 *
	 * @return bool|void
	 */
	public static function render( $view, $params = array(), $__required = true ) {
		$__views_path = static::locate( $view, $__required );
		if ( empty( $__views_path ) ) {
			return false;
		}

		if ( ! empty( $params ) ) {
			extract( $params );
		}

		include $__views_path;
	}

	/**
	 * Return real filename of view, or false if not exists
	 * if view is required then throw error
	 *
	 * @param string  $view      view shortname.
	 * @param boolean $required  throw exception if not found.
	 *
	 * @return string|false
	 *
	 * @throws \Exception  Required and not found.
	 */
	public static function locate( $view, $required = false ) {
		$view_file = "views/$view.php";
		$template  = locate_template( $view_file );
		if ( $required && ( empty( $template ) || ! is_file( $template ) ) ) {
			throw new \Exception( "Unable to locate views template: $view_file" );
		}

		return $template;
	}
}
