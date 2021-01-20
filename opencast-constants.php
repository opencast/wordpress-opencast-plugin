<?php
/**
 * @package OpencastPlugin
 */

//defining Constants
$original_file_path = str_replace('-constants' , '', __FILE__);
if (file_exists($original_file_path)) {
    define('PLUGIN_NAME', 'opencast_plugin');
    define('PLUGIN_BASENAME', plugin_basename($original_file_path));
    define('PLUGIN_DIR', plugin_dir_path($original_file_path));
    define('PLUGIN_DIR_URL', plugin_dir_url($original_file_path));
    define('PLUGIN_VERSION', '1.0.0');
    define('OPENCAST_OPTIONS', PLUGIN_NAME . '_options');
    define('OPENCAST_VIDEOS_MANAGER', PLUGIN_NAME . '_manager');
}

