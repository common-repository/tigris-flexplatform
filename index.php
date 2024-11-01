<?php
/*
Plugin Name: Tigris Flexplatform
Description: Integration Tigris user application for Salesforce in Wordpress.
Version: 1.0.2
Author: Tigris - Flexplatform
Author URI: https://www.tigris.nl

Text Domain: sf-tigris-flexplatform
Domain Path: /

License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

Copyright (C) 2019, Tigris - Flexplatform, info@tigris.nl
*/

/**
 * @package WordPress
 * @subpackage Tigris Flexplatform
 */

/**
 * Name space :: sf_tfp_*
 */

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

/** Set constant */
if ( ! defined( 'SF_TFP_DEV' )  ) {
	define( 'SF_TFP_DEV', 'Tigris Flexplatform' );
}
if ( ! defined( 'SF_TFP_NAME' )  ) {
	define( 'SF_TFP_NAME', basename( __DIR__ ) );
}
// Path
if ( ! defined( 'SF_TFP_PLUGIN_DIR' ) ) {
	define( 'SF_TFP_PLUGIN_DIR', __DIR__ );
}
// URL
if ( ! defined( 'SF_TFP_PLUGIN_URL' ) ) {
	define( 'SF_TFP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SF_TFP_ASSETS_URL' ) ) {
	define( 'SF_TFP_ASSETS_URL', SF_TFP_PLUGIN_URL . 'assets/' );
}

// Connect base text variable
include_once 'languages/lang-base.php';
// Connect setting function
include_once 'core/options.php';
// Connect main function
include_once 'core/function.php';

// Created hook - create extension setup page
if ( is_admin() ) {
	// Administrator actions
	add_action( 'admin_menu', 'sf_tfp_menu' );
	add_action( 'admin_init', 'sf_tfp_register_settings' );
} else {
	// Non-administrator enqueues, actions, and filters
	return;
}

/**
 * [sf_tfp_activated 					CORE: Activated Plug-In]
 * @return [hook]                   	[Result of processing the form]
 */
function sf_tfp_activated() {
	sf_tfp_add_tigris_role();
}
register_activation_hook( __FILE__, 'sf_tfp_activated' );

/**
 * [sf_tfp_deactivate 					CORE: Deactivated Plug-In]
 * @return [hook]                   	[Result of processing the form]
 */
function sf_tfp_deactivate() {
	sf_tfp_remove_tigris_role();
}
register_deactivation_hook( __FILE__, 'sf_tfp_deactivate' );