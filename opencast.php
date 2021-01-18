<?php
/**
 * @package OpencastPlugin
 */
/*
Plugin Name: Opencast Plugin
Plugin URI: https://github.com/opencast/wordpress-opencast-plugin
Description: Opencast Video Solution for automated video capture, management, and distribution at scale in wordpress.
Version: 1.0.0
Author: Farbod Zamani Boroujeni
Author Email: zamani@elan-ev.de
Author URI: elan-ev.de
Organisation: ELAN e.V
Licence: GPLv2 or later
Text Domain: elan-ev.de
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
 */

// Make sure we don't expose any info if called directly
defined('ABSPATH') or die('This is a pluging, direct call is prevented!');

//Make use of autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die('Unable to find autolaod!');
}

//defining Constants
define('PLUGIN_NAME', 'opencast_plugin');
define('PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('PLUGIN_VERSION', '1.0.0');
define('OPENCAST_OPTIONS', PLUGIN_NAME . '_options');
define('OPENCAST_VIDEOS_MANAGER', PLUGIN_NAME . '_manager');

// var_dump(plugins_url('/src/images/studio_small.svg', __FILE__));
//Handle activation / deactivation procedures

use Opencast\Base\Activate;
use Opencast\Base\Deactivate;

/**
 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
 */
function plugin_activate()
{
    Activate::activate();
}

/**
 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
 */
function plugin_deactivate()
{
    Deactivate::deactivate();
}

register_activation_hook(__FILE__, 'plugin_activate');
register_deactivation_hook(__FILE__, 'plugin_deactivate');

//Initialize the plugin
if (class_exists('Opencast\\Init')) {
    Opencast\Init::register_services();
}
