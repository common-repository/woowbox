<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since 1.0.0
 *
 * @global int $wp_version The version of WordPress for this install.
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( is_multisite() ) {
	global $wp_version;
	$site_list = version_compare( $wp_version, '4.6.0', '<' ) ? wp_get_sites() : get_sites();
	foreach ( (array) $site_list as $site ) {
		switch_to_blog( $site['blog_id'] );
		delete_option( 'woowbox_settings' );
		delete_option( 'woowbox_install_date' );
		delete_option( 'woowbox_notices' );
		delete_option( 'woowbox_version' );
		delete_option( 'woow_skins' );
		restore_current_blog();
	}
} else {
	delete_option( 'woowbox_settings' );
	delete_option( 'woowbox_install_date' );
	delete_option( 'woowbox_notices' );
	delete_option( 'woowbox_version' );
	delete_option( 'woow_skins' );
}
