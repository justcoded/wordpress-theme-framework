<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 5/12/17
 * Time: 16:56
 */

namespace JustCoded\WP\Framework\Page_Builder\v25;

if ( Page_Builder_Loader::widgets_bundle_active() ) {
	/**
	 * Class Page_Builder_Widget
	 */
	class Page_Builder_Widget extends \SiteOrigin_Widget {

		/**
		 * Preview images theme folder
		 *
		 * @var string
		 */
		public $preview_folder = 'assets/widgets';

		/**
		 * Page_Builder_Widget Constructor.
		 *
		 * @param string $id Widget ID.
		 * @param string $name Widget Name.
		 * @param array  $widget_options Optional Normal WP_Widget widget options and a few extras.
		 *   - help: A URL which, if present, causes a help link to be displayed on the Edit Widget modal.
		 *   - instance_storage: Whether or not to temporarily store instances of this widget.
		 *   - has_preview: Whether or not this widget has a preview to display. If false, the form does not output a
		 *                  'Preview' button.
		 * @param array  $control_options Optional Normal WP_Widget control options.
		 * @param array  $form_options Optional An array describing the form fields used to configure SiteOrigin widgets.
		 * @param mixed  $base_folder Optional  Some folder.
		 */
		public function __construct(
			$id,
			$name,
			$widget_options = array(),
			$control_options = array(),
			$form_options = array(),
			$base_folder = false
		) {
			$widget_options['panels_groups'] = array( 'recommended' );
			parent::__construct( $id, $name, $widget_options, $control_options, $form_options, $base_folder );

			// add hook to be able to overwrite widget label, printed in page builder rows grid.
			add_filter( 'siteorigin_widgets_sanitize_instance_' . $this->id_base, array( $this, 'before_update' ), 10, 3 );
		}

		/**
		 * Form fields configuration
		 *
		 * @return array
		 * @throws \Exception Method should be overwritten in nested class.
		 */
		public function get_widget_form() {
			throw new \Exception( 'Page_Builder_Widget::get_widget_form() : You should overwrite get_widget_form() method inside your own class.' );
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
			throw new \Exception( 'Page_Builder_Widget::modify_instance() : You should overwrite modify_instance() method inside your own class.' );
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
			throw new \Exception( 'Page_Builder_Widget::widget() : You should overwrite widget() method inside your own class.' );
		}

		/**
		 * Method called before saving instance to database.
		 *
		 * @param array               $new_instance Array of widget field values.
		 * @param array               $form_options Array of form field options.
		 * @param Page_Builder_Widget $widget Widget class instance.
		 *
		 * @return array
		 */
		public function before_update( $new_instance, $form_options, $widget ) {
			return $new_instance;
		}

		/**
		 * Get an array of variables to make available to templates. By default, just return an array. Should be overwritten by child widgets.
		 *
		 * @param array      $instance Instance values.
		 * @param array|null $args Args.
		 *
		 * @return array Instance validated values (with default values).
		 */
		public function get_template_variables( $instance, $args ) {
			$form_options = $this->get_widget_form();
			$instance     = $this->add_defaults( $form_options, $instance );
			$instance     = $this->modify_instance( $instance );

			return $instance;
		}

		/**
		 * Return a list of images to be shown as preview.
		 * By default it's widget {id_base}.png with $this->name
		 *
		 * @return array
		 */
		public function get_preview_images() {
			return array(
				$this->id_base . '.png' => $this->name . ' View Example',
			);
		}

		/**
		 * Print widget preview inside an iframe from Page_Builder previewer.
		 */
		public function preview() {
			$images = $this->get_preview_images();
			?>
			<p class="preview-note">
				<small>* Examples are shown with demo data.</small>
			</p>
			<div class="preview-items">
				<?php
				foreach ( $images as $img => $caption ) :
					if ( is_numeric( $img ) ) {
						$img     = $caption;
						$caption = $this->name;
					}
					if ( false === strpos( $img, 'http' ) ) {
						$img_path = '/' . $this->preview_folder . '/' . $img;
						if ( file_exists( get_stylesheet_directory() . $img_path ) ) {
							$img = get_stylesheet_directory_uri() . $img_path;
						} else {
							$img = get_template_directory_uri() . $img_path;
						}
					}
					?>
					<div class="preview-item text-center">
						<p class="image-caption"><?php echo esc_html( $caption ); ?></p>
						<p class="image"><img src="<?php echo esc_attr( $img ); ?>"></p>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( 1 < count( $images ) ) : ?>
				<div class="preview-nav">
					<a class="next pull-right" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
					<a class="prev pull-left" href="#"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
				</div>
			<?php endif; ?>
			<?php
		}

		/**
		 * Can be overwritten by child widgets to make variables available to javascript via ajax calls. These are designed to be used in the admin.
		 */
		public function get_javascript_variables() {

		}

	}
} else {
	/**
	 * Class Page_Builder_Widget
	 * if widgets bundle is not enabled we create a dummy class to prevent errors.
	 */
	class Page_Builder_Widget extends \WP_Widget {
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
		public function __construct(
			$id,
			$name,
			$widget_options = array(),
			$control_options = array(),
			$form_options = array(),
			$base_folder = false
		) {
		}
	}
}
