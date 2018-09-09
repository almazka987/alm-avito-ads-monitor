<?php
/*
 * Plugin Name: Alio Avito Ads Monitor
 * Version: 1.0
 * Plugin URI: https://github.com/aliowebdeveloper/alio-avito-ads-monitor
 * Description: Automatically monitoring ads on the Avito website by keywords and send notifications
 * Author: Alio Stels
 * Author URI: https://frantic-coding.000webhostapp.com/
 * Requires at least: 4.9
 * Tested up to: 4.9
 *
 * Text Domain: alio-avito-ads-monitor
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Alio Stels
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load plugin class files
require_once 'includes/class-alio-avito-ads-monitor.php';
require_once 'includes/class-alio-avito-ads-monitor-settings.php';

// Load plugin libraries
require_once 'includes/lib/class-alio-avito-ads-monitor-admin-api.php';
require_once 'includes/lib/phpQuery-onefile.php';

/**
 * Returns the main instance of Alio_Avito_Ads_Monitor to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Alio_Avito_Ads_Monitor
 */
function Alio_Avito_Ads_Monitor () {
    $instance = Alio_Avito_Ads_Monitor::instance( __FILE__, '1.0.0' );

    if ($instance->settings === null) {
        $instance->settings = Alio_Avito_Ads_Monitor_Settings::instance( $instance );
    }

    return $instance;
}

Alio_Avito_Ads_Monitor();