<?php

/**
 * 
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all plugin options from Data Base
 */

$aam_options = array( 'enable', 'city', 'keys', 'email' );
foreach ( $aam_options as $aam_option ) {
    if (get_option('aam_avito_' . $aam_option) !== null) {
        delete_option( 'aam_avito_' . $aam_option );
    }
}

/**
 * Delete plugin table from Data Base
 */

global $wpdb;
$wpdb->show_errors( true );
$table_name = $wpdb->prefix . 'alm_ads_monitor';
if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") ) {
    $res = $wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'alm_ads_monitor' );
}

