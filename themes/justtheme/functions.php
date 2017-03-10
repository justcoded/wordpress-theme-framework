<?php
/**
 * Just Theme functions and definitions.
 */

/**
 * We need to check that required plugins are active and installed
 */
require_once get_template_directory() . '/just-theme-framework-checker.php';
if ( ! $_jtf_checker->check_requirements() ) {
	// terminate if titan plugin is not activated.
	return;
}

require_once get_template_directory() . '/inc/template-funcs.php';

/**
 * Register theme namespace
 */
new \JustCoded\ThemeFramework\Autoload( 'justtheme\App', get_template_directory() . '/app' );


/**
 * Finally run our Theme setup
 * and ThemeOptions setup
 */
new \justtheme\App\Theme();
new \justtheme\App\Admin\ThemeSettings();