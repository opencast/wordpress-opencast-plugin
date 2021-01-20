<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

class ShortcodeController
{
    public function oc_add_inline_style($style_name, $style_content) {
        if (empty($style_name) || empty($style_content)) {
            return;
        }

        if (wp_register_style( $style_name, false )) {
            wp_enqueue_style( $style_name );
            wp_add_inline_style( $style_name, $style_content );
        }
    }
}