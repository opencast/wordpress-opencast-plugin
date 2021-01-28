<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Base;

class OCSettingLinks
{
    /**
     * Class registeration.
     */
    public function register()
    {
        add_filter('plugin_action_links_' . OPENCAST_PLUGIN_BASENAME , [$this, 'settings_link']);
    }

    /**
	 * Redirects to Opencast Admin Page
	 */
    public function settings_link($links) 
    {
        $links[] = '<a href="admin.php?page=' . OPENCAST_OPTIONS . '">Settings</a>';
        return $links;
    }
}