<?php
/**
 * @package OpencastPlugin
 */

//defining Constants
$original_file_path = str_replace('-constants' , '', __FILE__);
if (file_exists($original_file_path)) {
    define('OPENCAST_PLUGIN_NAME', 'opencast_plugin');
    define('OPENCAST_PLUGIN_BASENAME', plugin_basename($original_file_path));
    define('OPENCAST_PLUGIN_DIR', plugin_dir_path($original_file_path));
    define('OPENCAST_PLUGIN_DIR_URL', plugin_dir_url($original_file_path));
    define('OPENCAST_PLUGIN_VERSION', '1.0.0');
    define('OPENCAST_OPTIONS', OPENCAST_PLUGIN_NAME . '_options');
    define('OPENCAST_VIDEOS_MANAGER', OPENCAST_PLUGIN_NAME . '_manager');
}

