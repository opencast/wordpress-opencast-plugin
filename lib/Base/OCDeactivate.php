<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Base;

class OCDeactivate {
    /**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 */
    static function deactivate() 
    {
        // flush rewrite rules
        flush_rewrite_rules();
    }
}
?>