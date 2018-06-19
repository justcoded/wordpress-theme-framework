<?php

namespace JustCoded\WP\Framework\Supports;

use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class FakerPress
 * Fakerpress plugin extension which allows to generated faker content for custom fields.
 */
class FakerPress {
	use Singleton;

	/**
	 * Post Type ID.
	 *
	 * @var string $ID
	 */
	public $ID;

	/**
	 * Post Type Object.
	 *
	 * @var object $post_type
	 */
	public $post_type;

	/**
	 * FakerContent construct.
	 *
	 * @param string $id        Post Type ID.
	 * @param object $post_type Object.
	 */
	public function __construct( $id, $post_type ) {
		$this->ID        = $id;
		$this->post_type = $post_type;
		if ( self::do_fakerpress() ) {
			add_action( 'wp_insert_post_data', array( $this, 'pre_insert_post' ), 20, 2 );
			add_action( 'wp_insert_post', array( $this, 'insert_post' ), 10, 3 );
		}
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
			$this->do_save( $post_id, $this->post_type->faker() );
		}
	}

	/**
	 * Pre-save post content.
	 *
	 * @param array $data    An array of sanitized attachment post data.
	 * @param array $postarr An array of unsanitized attachment post data.
	 *
	 * @return array
	 */
	public function pre_insert_post( $data, $postarr ) {
		$faker_data = $this->post_type->faker();
		if ( $faker_data['post_title'] ) {
			$data['post_title'] = $faker_data['post_title'];
		}
		if ( $faker_data['post_content'] ) {
			$data['post_content'] = $faker_data['post_content'];
		}

		return $data;
	}

	/**
	 * Saved faker content.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Form data.
	 *
	 * @return bool
	 */
	public function do_save( $post_id, $data ) {
		if ( $data['post_featured_image'] ) {
			set_post_thumbnail( $post_id, $data['post_featured_image'] );
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
	public static function do_fakerpress() {
		if ( isset( $_POST['fakerpress']['view'] ) && $_POST['fakerpress']['view'] === 'posts' ) {
			return true;
		} else {
			return false;
		}
	}
}