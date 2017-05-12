<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 5/12/17
 * Time: 16:56
 */

namespace JustCoded\ThemeFramework\PageBuilder\v25;

class PageBuilderWidget extends \SiteOrigin_Widget {
	/**
	 * Form fields configuration
	 *
	 * @return array
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @throws \Exception
	 */
	public function widget( $args, $instance ) {
		throw new \Exception( 'PageBuilderWidget::widget() : You should overwrite widget() method inside your own class.' );
	}
}