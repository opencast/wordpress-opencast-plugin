<?php 
/**
 * Uninstall Opencast Plugin
 * 
 *  @package OpencastPlugin
 */

// Make sure uninstall is called correctly!
defined( 'WP_UNINSTALL_PLUGIN' ) or die();

//Make use of constants
if (file_exists(__DIR__ . '/opencast-constants.php')) {
    require_once __DIR__  . '/opencast-constants.php';
    // flush rewrite rules
    flush_rewrite_rules();
    if (get_option(OPENCAST_OPTIONS)) {
        delete_option(OPENCAST_OPTIONS); // delete opencast option
    }
}
?>