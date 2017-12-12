<?php
namespace JustCoded\WP\Framework\Web;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Views base class.
 * Used for layouts and render partials
 */
class View {
	use Singleton;

	/**
	 * Layouts call chain
	 *
	 * @var array
	 */
	private static $extends = array();

	public $template;

	protected function __construct() {
		add_filter('template_include', array($this, 'init_template'), 999999);
	}

	public function init_template($template) {
		$this->template = $template;

		return $this;
	}

	public function __toString() {
		return locate_template( array( 'index.php' ) );
	}

	public function include_template() {
		include $this->template;

		$this->wrap();
	}

	public static function wrap() {
		if ( empty( static::$extends ) ) {
			return false;
		}

		while( ob_get_contents() && $template = array_pop( static::$extends ) ) {
			$content = ob_get_contents();

			// clean view file buffer.
			ob_clean();

			// reset query to protect header from unclosed query in the content.
			wp_reset_postdata();

			// render under the existing context.
			include $template;
		}
	}

	public static function extends( $layout = 'main' ) {
		if ( false === $layout ) {
			return false;
		}

		// WordPress compatibility to still process usual headers.
		if ( empty( static::$extends ) ) {
			do_action( 'get_header', null );
		}

		// check that we have required template.
		$template = static::locate( $layout, true );

		// memorize the template.
		array_push( static::$extends, $template );

		// start buffer.
		ob_start();
		return true;
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
	 * @param string|null $name custom footer name.
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
	 * @return bool
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
		return true;
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
