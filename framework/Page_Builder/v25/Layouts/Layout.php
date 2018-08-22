<?php

namespace JustCoded\WP\Framework\Page_Builder\v25\Layouts;

/**
 * Class Layout
 * Starting from Page Builder v2.5 all inline css were moved into separate inline css block.
 * Since we remove all inline css from Page_Builder - we need to enable back this feature.
 *
 * For image attachment we can use rwd plugin for better support.
 */
class Layout {
	/**
	 * Image size for background image in case of RWD plugin enabled.
	 *
	 * @var string
	 */
	public $background_image_size = 'large';

	/**
	 * Generate inline styles, from Design Page Builder options
	 *
	 * @param array  $style_data    Style options array.
	 * @param string $unique_selector  Unique block selector (for rwd function).
	 *
	 * @return array
	 */
	protected function generate_inline_styles( $style_data, $unique_selector = '' ) {
		$styles = array();

		// generate bg color.
		if ( ! empty( $style_data['background'] ) ) {
			$styles['bg_color'] = 'background-color: ' . $style_data['background'];
		}

		// generate border color.
		if ( ! empty( $style_data['border_color'] ) ) {
			$styles['border'] = 'border: 1px solid ' . $style_data['border_color'];
		}

		// print background and bg position.
		if ( ! empty( $style_data['background_image_attachment'] ) ) {
			// in case we have rwd plugin - print bg in responsive manner.
			if ( is_plugin_active( 'just-responsive-images/just-responsive-images.php' ) ) {
				rwd_attachment_background( ".$unique_selector", $style_data['background_image_attachment'], $this->background_image_size );
			} elseif ( $url = wp_get_attachment_image_url( $style_data['background_image_attachment'], 'full' ) ) {
				// otherwise just use full image as bg.
				$styles['bg_image'] = 'background-image: url(' . $url . ')';
			}

			switch ( $style_data['background_display'] ) {
				case 'parallax':
				case 'parallax-original':
					$styles['bg_pos']    = 'background-position:center center';
					$styles['bg_repeat'] = 'background-repeat:no-repeat';
					break;
				case 'tile':
					$styles['bg_repeat'] = 'background-repeat:repeat';
					break;
				case 'cover':
					$styles['bg_pos']  = 'background-position:center center';
					$styles['bg_size'] = 'background-size:cover';
					break;
				case 'center':
					$styles['bg_pos']    = 'background-position:center center';
					$styles['bg_repeat'] = 'background-repeat:no-repeat';
					break;
				case 'fixed':
					$styles['bg_attach'] = 'background-attachment:fixed';
					$styles['bg_pos']    = 'background-position:center center';
					$styles['bg_size']   = 'background-size:cover';
					break;
			}
		}

		$styles = implode( ';', $styles );

		return $styles;
	}

}
