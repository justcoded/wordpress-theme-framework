<?php

namespace JustCoded\WP\Framework\Supports;

use Faker\Provider\Uuid;
use Faker\Provider\TextBase;
use Faker\Factory;
use JustCoded\WP\Framework\Objects\Singleton;

/**
 * Class FakerContent
 * FakerPress plugin extension which allows to generated faker content for custom fields.
 *
 * @method static FakerContent instance() Singleton design pattern instance creation.
 */
class FakerContent {
	use Singleton;

	/**
	 * Faker object
	 *
	 * @var $faker
	 */
	public $faker;

	/**
	 * FakerPress plugin custom providers
	 *
	 * @var array
	 */
	public $providers = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	/**
	 * FakerContent construct
	 */
	protected function __construct() {
		$this->faker = Factory::create();

		// register custom providers.
		foreach ( $this->providers as $provider_class ) {
			$provider = new $provider_class( $this->faker );
			$this->faker->addProvider( $provider );
		}
	}

	/**
	 * Generated array for flexible content.
	 *
	 * @param array $data Flexible content data.
	 *
	 * @return array
	 */
	public function flexible_content( $data = array() ) {
		if ( empty( $data ) || ! class_exists( 'acf' ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Prepare flexible layout data array
	 *
	 * @param string $layout_name Flexible content layout name.
	 * @param array  $data Layout fields data.
	 *
	 * @return array
	 */
	public function flexible_layout( $layout_name, $data ) {
		return array_merge( $data, array( 'acf_fc_layout' => $layout_name ) );
	}

	/**
	 * Generated array for repeater fields.
	 *
	 * @param int|int[] $qty Min/max qty to generate.
	 * @param callable  $callback Repeater fields data generator callback.
	 *
	 * @return array
	 */
	public function repeater( $qty, $callback ) {
		// validate qty.
		$qty = $this->normalize_qty( $qty );
		if ( $qty[0] < 1 ) {
			return array();
		}

		// generate data.
		$data = array();
		$num  = $this->faker->numberBetween( $qty[0], $qty[1] );
		for ( $i = 0; $i < $num; $i++ ) {
			$data[] = $callback();
		}

		return $data;
	}

	/**
	 * Normalize qty to standard range array.
	 *
	 * @param int|int[] $qty Number or range.
	 *
	 * @return array|bool
	 */
	protected function normalize_qty( $qty ) {
		if ( ! is_array( $qty ) ) {
			$qty = array( $qty );
		}
		if ( count( $qty ) < 2 && (int) $qty[1] < (int) $qty[0] ) {
			$qty[1] = $qty[0];
		}
		$qty[0] = (int) $qty[0];
		$qty[1] = (int) $qty[1];

		if ( $qty[0] < 1 ) {
			return false;
		}

		return [
			$qty[0],
			$qty[1],
		];
	}

	/**
	 * Get fake text.
	 *
	 * @param int $max_chars Chars number.
	 *
	 * @return string
	 */
	public function text( $max_chars = 200 ) {
		return TextBase::text( $max_chars );
	}


	/**
	 * Get fake html.
	 *
	 * @param array  $content_size Min/max number of paragraphs to generate.
	 * @param string $html_tags Allowed html tags.
	 *
	 * @return string
	 */
	public function html_text(
		$content_size = [ 5, 10 ],
		$html_tags = 'p,p,p,p,p,p,p,h2,h3,h4,h5,h6,ul,ol,p,blockquote,img,hr,b,i,a' // increase "p" possibility.
	) {
		$tags = $this->faker->html_elements([
			'qty'      => $content_size,
			'elements' => explode( ',', $html_tags ),
		]);
		return implode( "\n", $tags );
	}

	/**
	 * Get fake words.
	 *
	 * @param int|int[] $qty Words number or range.
	 *
	 * @return string
	 */
	public function words( $qty = 3 ) {
		$qty = $this->normalize_qty( $qty );
		if ( $qty[0] < 1 ) {
			return '';
		}

		$nb = $this->faker->numberBetween( $qty[0], $qty[1] );
		return $this->faker->words( $nb, true );
	}

	/**
	 * Generated and save attachment file.
	 *
	 * @param int    $width  Attachment width.
	 * @param int    $height Attachment height.
	 * @param string $type   Attachment type( id, url ).
	 *
	 * @return int|string
	 */
	public function image_attachment( $width = 1200, $height = 800, $type = 'id' ) {
		$color      = substr( md5( microtime( true ), false ), 0, 6 );
		$text       = $this->faker->words( 2, true );
		$attach_url = "http://via.placeholder.com/{$width}x{$height}/$color/?text=$text";

		if ( 'id' !== $type ) {
			return $attach_url;
		}

		$response = wp_remote_get( $attach_url, array( 'timeout' => 5 ) );

		// Bail early if we have an error.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$bits = wp_remote_retrieve_body( $response );

		// Prevent Weird bits.
		if ( false === $bits ) {
			return false;
		}

		$filename = Uuid::uuid() . '.jpg';
		$upload   = wp_upload_bits( $filename, '', $bits );

		$attachment = array(
			'guid'           => $upload['url'],
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $filename,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the attachment.
		$attach_id   = wp_insert_attachment( $attachment, $upload['file'], 0 );
		$attach_path = get_attached_file( $attach_id );
		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $attach_path ) );

		return $attach_id;
	}

	/**
	 * Get fake number.
	 *
	 * @return int
	 */
	public function percent() {
		return $this->faker->numberBetween( 0, 100 );
	}

	/**
	 * Generate random number.
	 *
	 * @param int $min min value.
	 * @param int $max max value.
	 *
	 * @return int
	 */
	public function number( $min = 1, $max = 99 ) {
		return $this->faker->numberBetween( $min, $max );
	}

	/**
	 * Get fake date.
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function date( $format = 'Y-m-d' ) {
		return $this->faker->date( $format );
	}

	/**
	 * Get fake timezone.
	 *
	 * @return string
	 */
	public function timezone() {
		return $this->faker->timezone;
	}

	/**
	 * Get fake person name.
	 *
	 * @return string
	 */
	public function person() {
		return $this->faker->name;
	}

	/**
	 * Get fake company name.
	 *
	 * @return string
	 */
	public function company() {
		return $this->faker->company;
	}

	/**
	 * Get fake job title.
	 *
	 * @return string
	 */
	public function job_title() {
		return $this->faker->jobTitle;
	}

	/**
	 * Get fake email.
	 *
	 * @return string
	 */
	public function email() {
		return $this->faker->safeEmail;
	}

}
