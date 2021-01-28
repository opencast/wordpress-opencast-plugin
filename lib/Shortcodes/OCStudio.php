<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

use Opencast\Api\OCLTIConsumer;

class OCStudio extends OCShortcodeController
{
     /**
     * Class registeration.
     */
    public function register()
    {
        if (!get_option(OPENCAST_OPTIONS)) {
            return;
        }
        add_shortcode('opencast-studio-button', [$this, 'generate_opencast_studio_form']);
    }

    public function generate_opencast_studio_form($attr)
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $studiousepermissions = (isset($opencast_options['studiousepermissions'])) ? $opencast_options['studiousepermissions'] : true;
        if ($studiousepermissions) {
            $studiopermissions = (isset($opencast_options['studiopermission']) && $opencast_options['studiopermission']) ? 
                                        ((!is_array($opencast_options['studiopermission'])) ? array($opencast_options['studiopermission']) : $opencast_options['studiopermission'])
                                        : array();
            if ($studiopermissions && is_user_logged_in()) {
                $user = wp_get_current_user();
                $user_roles = ( array ) $user->roles;
                if (empty(array_intersect($user_roles,$studiopermissions))) {
                    return false;
                }    
            } else {
                return false;
            }
        }
        
        $studio = '';
        $title = (!empty($attr['title']) ?  __(implode(' ', array_map('sanitize_text_field', explode(' ', $attr['title'])))) : '');
        $class = (!empty($attr['class']) ?  implode(' ', array_map('sanitize_text_field', explode(' ', $attr['class']))) : '');
        //No sanitization needed.
        if (!isset($_GET['redirect_to_studio'])) {
            $studio_style = '';
            if (!$class) {
                $studio_css = $this->generate_default_style();
                $studio_style_name = 'oc-studio-btn-style';
                $this->oc_add_inline_style($studio_style_name, $studio_css);
                $studio_style = "style='background-image: url(" . plugins_url('/src/images/studio_small.svg', dirname(__FILE__, 2)) .");'";
                $class = 'oc-studio-btn';
            }
            $studio .= "<a target='_blank' $studio_style class='" . esc_attr($class) . "' href='?redirect_to_studio=1' class='" . esc_attr($class) . "'>" . esc_html($title) . "</a>";
            return $studio;
        }
        //Query settings
        $workflowId = ((isset($opencast_options['uploadworkflow']) && !empty($opencast_options['uploadworkflow'])) ? "{$opencast_options['uploadworkflow']}" : "upload");
        $seriesid = $opencast_options['seriesid'];

        $querys = array(
            "upload.seriesId=$seriesid",
            "upload.acl=false",
            "upload.workflowId=$workflowId"
        );

        $consumerkey = $opencast_options['consumerkey'];
        $consumersecret = $opencast_options['consumersecret'];
        $endpoint = rtrim($opencast_options['apiurl'], '/') . '/lti';
        $customtools = "studio/index.html?" . implode('&', $querys);
        $studio = OCLTIConsumer::lti_launch($endpoint, $consumerkey, $consumersecret, $customtools, true, array('ROLE_STUDIO'));
        
        return $studio;
    }

    private function generate_default_style() {
        return ".oc-studio-btn{background-repeat:no-repeat;background-color:#363636;background-position-x:10px;background-position-y:center;background-attachment:scroll;background-size:70px;background-origin: padding-box;background-clip: border-box;padding:16px 16px 16px 80px;border-radius:10px;text-decoration:none;color:#fff;}";
    }
}

?>