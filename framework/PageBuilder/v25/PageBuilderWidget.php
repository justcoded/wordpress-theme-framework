<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 5/12/17
 * Time: 16:56
 */

namespace JustCoded\ThemeFramework\PageBuilder\v25;

if ( ! PageBuilderLoader::widgets_bundle_active() ) {
	/**
	 * Class PageBuilderWidget
	 * if widgets bundle is not enabled we create a dummy class to prevent errors.
	 */
	class PageBuilderWidget extends \WP_Widget {
		/**
		 * Constructor.
		 *
		 * @param string $id Widget id.
		 * @param string $name Widget title.
		 * @param array  $widget_options Optional Normal WP_Widget widget options and a few extras.
		 *   - help: A URL which, if present, causes a help link to be displayed on the Edit Widget modal.
		 *   - instance_storage: Whether or not to temporarily store instances of this widget.
		 *   - has_preview: Whether or not this widget has a preview to display. If false, the form does not output a
		 *                  'Preview' button.
		 * @param array  $control_options Optional Normal WP_Widget control options.
		 * @param array  $form_options Optional An array describing the form fields used to configure SiteOrigin widgets.
		 * @param mixed  $base_folder Optional Some compatibility param.
		 */
		function __construct( $id, $name, $widget_options = array(), $control_options = array(), $form_options = array(), $base_folder = false ) {
		}
	}

	return false;
}

/**
 * Class PageBuilderWidget
 */
class PageBuilderWidget extends \SiteOrigin_Widget {
	/**
	 * Form fields configuration
	 *
	 * @return array
	 * @throws \Exception Method should be overwritten in nested class.
	 */
	public function get_widget_form() {
		throw new \Exception( 'PageBuilderWidget::get_widget_form() : You should overwrite get_widget_form() method inside your own class.' );
	}

	/**
	 * Modify form submitted values before save.
	 *
	 * @param array $instance submitted form values.
	 *
	 * @return array
	 * @throws \Exception Method should be overwritten in nested class.
	 */
	public function modify_instance( $instance ) {
		throw new \Exception( 'PageBuilderWidget::modify_instance() : You should overwrite modify_instance() method inside your own class.' );
	}

	/**
	 * Print widget method.
	 *
	 * @param array $args Widget display arguments.
	 * @param array $instance Widget settings.
	 *
	 * @throws \Exception Method should be overwritten in nested class.
	 */
	public function widget( $args, $instance ) {
		throw new \Exception( 'PageBuilderWidget::widget() : You should overwrite widget() method inside your own class.' );
	}
}