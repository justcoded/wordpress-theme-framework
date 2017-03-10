<?php
namespace justtheme\App\Admin;

/**
 * App backend settings page options
 */
class ThemeSettings extends \JustCoded\ThemeFramework\Admin\ThemeSettings {
	/**
	 * Create admin page for theme settings
	 */
	public function init() {
		// init framework instance.
		self::check_instance();

		$panel = self::$tf->createContainer( array(
			'type' => 'admin-page',
			'name' => 'Just Theme Options',
		) );

		$this->add_panel_tabs( $panel, array(
			'general' => 'General',
			'social'  => 'Social links',
			'404'     => '404 Page',
		) );
	}

	/**
	 * Register fields for General tab
	 *
	 * @param \TitanFrameworkAdminPage $panel  panel object to work with.
	 */
	protected function registerGeneralTab( $panel ) {
		$tab = $panel->createTab( array(
			'name' => 'General',
		) );

		$tab->createOption( array(
			'name' => 'Footer ',
			'type' => 'heading',
		) );

		$tab->createOption( array(
			'name'    => 'Copyright text',
			'id'      => 'copyright_text',
			'type'    => 'text',
			'default' => '&copy; ' . date( 'Y' ) . '. All rights reserved.',
		) );


		$tab->createOption( array(
			'type' => 'save',
		) );
	}

	/**
	 * Register fields for Social links tab.
	 *
	 * @param \TitanFrameworkAdminPage $panel  panel object to work with.
	 */
	protected function registerSocialTab( $panel ) {
		$tab = $panel->createTab( array(
			'name' => 'Social links',
		) );

		$tab->createOption( array(
			'name'        => 'Facebook page',
			'id'          => 'social_fb',
			'type'        => 'text',
			'placeholder' => 'http://facebook.com/my-page',
		) );

		$tab->createOption( array(
			'name'        => 'Twitter account',
			'id'          => 'social_twitter',
			'type'        => 'text',
			'placeholder' => 'http://twitter.com/@some-username',
		) );

		$tab->createOption( array(
			'name'        => 'Google+',
			'id'          => 'social_gplus',
			'type'        => 'text',
			'placeholder' => 'https://plus.google.com/-unique-profile-id-',
		) );

		$tab->createOption( array(
			'type' => 'save',
		) );
	}

	/**
	 * Register fields for 404 tab.
	 *
	 * @param \TitanFrameworkAdminPage $panel  panel object to work with.
	 */
	protected function register404Tab( $panel ) {
		$tab = $panel->createTab( array(
			'name' => '404 Page',
		) );

		$tab->createOption( array(
			'name'    => 'Title',
			'id'      => '404_title',
			'type'    => 'text',
			'default' => __( 'Oops! That page can&rsquo;t be found.', 'justtheme' ),
		) );

		$tab->createOption( array(
			'name'    => 'Content',
			'id'      => '404_content',
			'type'    => 'editor',
			'default' => __( 'It looks like nothing was found at this location. Maybe try one of the links in menu or a search?', 'justtheme' ),
		) );

		$tab->createOption( array(
			'type' => 'save',
		) );
	}
}