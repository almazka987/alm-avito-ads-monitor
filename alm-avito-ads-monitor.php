<?php
/*
 * Plugin Name: Alm Avito Ads Monitor
 * Version: 1.0
 * Plugin URI: https://github.com/almazka987/alm-avito-ads-monitor
 * Description: Automatically monitoring ads on the Avito website by keywords and send notifications
 * Author: Alio Stels
 * Author URI: https://frantic-coding.000webhostapp.com/
 * Requires at least: 4.9
 * Tested up to: 4.9
 *
 * Text Domain: alm-avito-ads-monitor
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
require_once 'includes/class-alm-avito-ads-monitor.php';
require_once 'includes/class-alm-avito-ads-monitor-settings.php';

// Load plugin libraries
require_once 'includes/lib/class-alm-avito-ads-monitor-admin-api.php';
require_once 'includes/lib/phpQuery-onefile.php';

/**
 * Returns the main instance of Alm_Avito_Ads_Monitor to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Alm_Avito_Ads_Monitor
 */
function Alm_Avito_Ads_Monitor () {
    $instance = Alm_Avito_Ads_Monitor::instance( __FILE__, '1.0.0' );

    if ($instance->settings === null) {
        $instance->settings = Alm_Avito_Ads_Monitor_Settings::instance( $instance );
    }

    return $instance;
}

Alm_Avito_Ads_Monitor();