<?php
/**
 * @package OpencastPlugin   
*/
namespace Opencast\Base;

class Activate {
    /**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 */
    static function activate() 
    {
        // flush rewrite rules
        flush_rewrite_rules();
        if ( get_option(OPENCAST_OPTIONS) ) {
            // update_option( OPENCAST_OPTIONS, []); // keep previous data
        }
    }
}
?>