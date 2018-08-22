<?php
namespace JustCoded\WP\Framework\Supports;

use FakerPress\Module\Post;
use JustCoded\WP\Framework\Objects\Post_Type;

/**
 * Class FakerPress
 * Fakerpress plugin extension which allows to generated faker content for custom fields.
 */
class FakerPress {

	/**
	 * Post Type ID.
	 *
	 * @var string $ID
	 */
	protected $ID;

	/**
	 * Post type definition object.
	 *
	 * @var Post_Type
	 */
	public $post_type;

	/**
	 * Post Type faker data.
	 *
	 * @var array $data
	 */
	public $data;

	/**
	 * FakerContent construct.
	 *
	 * @param Post_Type $post_type Object.
	 */
	public function __construct( $post_type ) {
		if ( $this->do_fakerpress() ) {
			$this->ID        = $post_type::$ID;
			$this->post_type = $post_type;

			add_action( 'wp_insert_post_data', array( $this, 'pre_insert_post' ), 20, 2 );
			add_action( 'wp_insert_post', array( $this, 'insert_post' ), 10, 3 );
		}
	}

	/**
	 * Pre-save post content.
	 *
	 * @param array $data    An array of sanitized attachment post data.
	 * @param array $post_data An array of not sanitized attachment post data.
	 *
	 * @return array
	 */
	public function pre_insert_post( $data, $post_data ) {
		$this->data = $this->post_type->faker();

		if ( $this->ID === $data['post_type'] ) {
			if ( isset( $this->data['post_title'] ) ) {
				$data['post_title'] = $this->data['post_title'];
				unset( $this->data['post_title'] );
			}
			if ( isset( $this->data['post_content'] ) ) {
				$data['post_content'] = $this->data['post_content'];
				unset( $this->data['post_content'] );
			}
		}

		return $data;
	}

	/**
	 * Fires once a post has been saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public function insert_post( $post_id, $post, $update ) {
		if ( $this->ID === $post->post_type ) {
			$this->update_post_meta( $post_id, $this->data );
		}
	}

	/**
	 * Saved faker content.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Form data.
	 *
	 * @return bool
	 */
	public function update_post_meta( $post_id, $data ) {
		if ( isset( $data['post_featured_image'] ) ) {
			set_post_thumbnail( $post_id, $data['post_featured_image'] );
			unset( $data['post_featured_image'] );
		}

		foreach ( $data as $meta_key => $meta_value ) {
			if ( class_exists( 'acf' ) ) {
				update_field( $meta_key, $meta_value, $post_id );
			} else {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

		return true;
	}

	/**
	 * Check that required plugin is installed and activated
	 *
	 * @return bool
	 */
	public static function check_requirements() {
		return is_plugin_active( 'fakerpress/fakerpress.php' );
	}

	/**
	 * Check work plugin Fakerpress for generated faker content.
	 *
	 * @return bool
	 */
	public function do_fakerpress() {
		if ( isset( $_POST['fakerpress']['view'] ) && 'posts' === $_POST['fakerpress']['view'] ) {
			return true;
		} else {
			return false;
		}
	}
}
