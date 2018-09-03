<?php
/*
 * Plugin Name: Alio Ads Monitor
 * Version: 1.0
 * Plugin URI: https://frantic-coding.000webhostapp.com/
 * Description: Automatically monitoring ads on the websites by keywords
 * Author: Alio Stels
 * Author URI: https://frantic-coding.000webhostapp.com/
 * Requires at least: 4.9
 * Tested up to: 4.9
 *
 * Text Domain: alio-ads-monitor
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Alio Stels
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-alio-ads-monitor.php' );
require_once( 'includes/class-alio-ads-monitor-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-alio-ads-monitor-admin-api.php' );
require_once( 'includes/lib/phpQuery-onefile.php' );

/**
 * Returns the main instance of Alio_Ads_Monitor to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Alio_Ads_Monitor
 */
function Alio_Ads_Monitor () {
    $instance = Alio_Ads_Monitor::instance( __FILE__, '1.0.0' );

    if ( is_null( $instance->settings ) ) {
        $instance->settings = Alio_Ads_Monitor_Settings::instance( $instance );
    }

    return $instance;
}

Alio_Ads_Monitor();