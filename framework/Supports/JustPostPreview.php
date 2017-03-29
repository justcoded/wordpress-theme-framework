<?php
namespace JustCoded\ThemeFramework\Supports;

/**
 * Class JustPostPreview
 *
 * Patch the folder to search custom templates.
 *
 * @package JustCoded\ThemeFramework\Supports
 */
class JustPostPreview {

	/**
	 * JustPostPreview constructor.
	 *
	 * Register plugin hooks.
	 */
	public function __construct() {
		add_filter( 'jpp_post_preview_template', array( $this, 'jpp_post_preview_template' ) );
	}

	/**
	 * Add new templates inside /views/ folder
	 *
	 * @param array $templates Templates suggested by plugin.
	 *
	 * @return mixed
	 */
	public function jpp_post_preview_template( $templates ) {
		if ( ! empty( $templates ) ) {
			$template = basename( reset( $templates ) );

			$this->patches_templates = array_merge( array(
				get_stylesheet_directory() . '/views/just-post-preview/' . $template,
				get_template_directory() . '/views/just-post-preview/' . $template,
			), $templates );
		}

		return $templates;
	}
}
