<?php
namespace JustCoded\WP\Framework\Web;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * View base class.
 * Used for layouts and render partials
 *
 * @method View instance()
 */
class View {
	use Singleton;

	/**
	 * Layouts call chain.
	 *
	 * @var array
	 */
	private $extends = array();

	/**
	 * Theme template path to be loaded.
	 *
	 * @var string
	 */
	public $template;

	/**
	 * Template params, which you can use to pass variables through inherited templates.
	 *
	 * @var array
	 */
	public $params = array();

	/**
	 * View constructor.
	 *
	 * Executed immediately before WordPress includes the predetermined template file
	 * Override WordPress's default template behavior.
	 */
	protected function __construct() {
		add_filter( 'template_include', array( $this, 'init_template' ), 999999 );
	}

	/**
	 * Implement template wrappers. For this we need to remember real template to be loaded.
	 * Then we return the object itself to be able to manipulate the loading process.
	 *
	 * @param string $template Template to be included inside theme.
	 *
	 * @return $this
	 */
	public function init_template( $template ) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Convert object to string magic method.
	 * We replaced string with object inside `template_include` hook, so to support next include statement we need
	 * to add magic method, which make object to string conversion.
	 *
	 * Here we will just return theme index.php file, which will be the entry point of our views engine.
	 *
	 * @return string
	 */
	public function __toString() {
		return locate_template( array( 'index.php' ) );
	}

	/**
	 * Start loading theme template.
	 */
	public function run() {
		// add alias.
		$template = $this->template;

		include $this->template;

		$this->wrap();
	}

	/**
	 * Wrap content with another view or layout
	 *
	 * @return bool|void
	 */
	public function wrap() {
		if ( empty( $this->extends ) ) {
			return false;
		}

		while ( ob_get_contents() && $template = array_pop( $this->extends ) ) {
			$content = ob_get_contents();

			// clean view file buffer.
			ob_clean();

			// reset query to protect header from unclosed query in the content.
			wp_reset_postdata();

			// render under the existing context.
			include $template;
		}
	}

	/**
	 * Registers parent template.
	 * Parent template will be rendered just after current template execution.
	 *
	 * To use current template generated html use `$content` variable inside the parent view
	 *
	 * @param string $layout  View name to register.
	 *
	 * @return bool
	 * @throws \Exception If no parent view template found.
	 */
	public function extends( $layout = 'layouts/main' ) {
		if ( false === $layout ) {
			return false;
		}

		// WordPress compatibility to still process usual headers.
		if ( empty( $this->extends ) ) {
			do_action( 'get_header', null );
		}

		// check that we have required template.
		$template = $this->locate( $layout, true );

		// memorize the template.
		array_push( $this->extends, $template );

		// start buffer.
		ob_start();
		return true;
	}

	/**
	 * WordPress compatibility option instead of get_sidebar, to run a sidebar hook
	 *
	 * @param string|null $name custom sidebar name.
	 */
	public function sidebar_begin( $name = null ) {
		// WordPress compatibility.
		do_action( 'get_footer', $name );
	}

	/**
	 * WordPress compatibility option instead of get_sidebar
	 *
	 * @param string|null $name custom footer name.
	 */
	public function footer_begin( $name = null ) {
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
	public function include( $view, $params = array(), $__required = true ) {
		$template = $this->locate( $view, $__required );
		if ( empty( $template ) ) {
			return false;
		}

		if ( ! empty( $params ) ) {
			extract( $params );
		}

		include $template;
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
	public function locate( $view, $required = false ) {
		$view_file = "views/$view.php";
		$template  = locate_template( $view_file );
		if ( $required && ( empty( $template ) || ! is_file( $template ) ) ) {
			throw new \Exception( "Unable to locate views template: $view_file" );
		}

		return $template;
	}
}
