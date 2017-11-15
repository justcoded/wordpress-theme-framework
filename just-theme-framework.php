<?php

/*
Plugin Name: Just Theme Framework
Description: Lightweight MVC theming framework for developers who want to better organize their own custom themes with an MVC approach.
Tags: mvc theme, theme boilerplate, oop theme, mini framework
Version: 1.3.1
Author: JustCoded / Alex Prokopenko
Author URI: http://justcoded.com/
License: GPL3
*/

defined( 'JTF_PLUGIN_FILE' ) || define( 'JTF_PLUGIN_FILE', __FILE__ );

// Required functions as it is only loaded in admin pages.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Required spl autoload registration class.
require_once( dirname( __FILE__ ) . '/framework/Autoload.php' );

// Include small helper functions
require_once( dirname( __FILE__ ) . '/framework/helpers.php' );

new Just_Coded\Theme_Framework\Autoload( 'Just_Coded\Theme_Framework', dirname( __FILE__ ) . '/framework' );