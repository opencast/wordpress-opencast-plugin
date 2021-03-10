<?php
/**
 * @package OpencastPlugin
 */

//defining Constants

## INFO:
//regarding to correction ## Calling file locations poorly
// __FILE__ has been used to define constant OPENCAST_PLUGIN_DIR - later on it will be used throughout the plugin! 
$original_file_path = str_replace('-constants' , '', __FILE__);
if (file_exists($original_file_path)) {
    define('OPENCAST_PLUGIN_NAME', 'opencast_plugin');
    define('OPENCAST_PLUGIN_BASENAME', plugin_basename($original_file_path));
    define('OPENCAST_PLUGIN_DIR', plugin_dir_path($original_file_path));
    define('OPENCAST_PLUGIN_DIR_URL', plugin_dir_url($original_file_path));
    define('OPENCAST_PLUGIN_VERSION', '1.0.1');
    define('OPENCAST_OPTIONS', OPENCAST_PLUGIN_NAME . '_options');
    define('OPENCAST_VIDEOS_MANAGER', OPENCAST_PLUGIN_NAME . '_manager');
}

