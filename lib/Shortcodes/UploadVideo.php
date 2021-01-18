<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

use Opencast\Api\OCRestAPI;
use Opencast\Api\LTIConsumer;

define('POSTFILENAME', 'OCVideoToUpload');

class UploadVideo
{
     /**
     * Class registeration.
     */
    public function register()
    {
        if (!get_option(OPENCAST_OPTIONS)) {
            return;
        }
        add_shortcode('opencast-upload-button', [$this, 'generate_opencast_upload']);
    }

    public function generate_opencast_upload($attr)
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $user = wp_get_current_user();
        $uploadusepermissions = (isset($opencast_options['uploadusepermissions'])) ? $opencast_options['uploadusepermissions'] : true;
        if ($uploadusepermissions) {
            $uploadpermissions = (isset($opencast_options['uploadpermissions']) && $opencast_options['uploadpermissions']) ? 
                                        ((!is_array($opencast_options['uploadpermissions'])) ? array($opencast_options['uploadpermissions']) : $opencast_options['uploadpermissions'])
                                        : array();
            if ($uploadpermissions && is_user_logged_in()) {
                $user_roles = ( array ) $user->roles;
                if (empty(array_intersect($user_roles,$uploadpermissions))) {
                    return false;
                }    
            } else {
                return false;
            }
        }

        //Attributes via shortcode definition
        $text = (isset($attr['text']) && !empty($attr['text'])) ? __(esc_html( $attr['text'] )) : __('Drop your video file here');
        $btn_text = (isset($attr['btn_text']) && !empty($attr['btn_text'])) ? __(esc_html( $attr['btn_text'] )) : __('Upload Video To Opencast');
        $class = (isset($attr['class']) && !empty($attr['class'])) ?  __(esc_html( $attr['class'] )) : '';
        $type = ((isset($attr['type']) && !empty($attr['type'])) && (strtolower($attr['type']) == 'presenter' || strtolower($attr['type']) == 'presentation')) ? strtolower($attr['type']) : 'presenter';
        $success_text = (isset($attr['success_text']) && !empty($attr['success_text'])) ? $attr['success_text'] : __('Uploaded');
        $fail_text = (isset($attr['fail_text']) && !empty($attr['fail_text'])) ? $attr['fail_text'] : __('Failed');
        
        wp_enqueue_script( 'dropzone', PLUGIN_DIR_URL . 'src/vendors/dropzone/dist/dropzone.js', array('jquery'), '5.7.0' );
        wp_enqueue_script( 'upload-video.js', PLUGIN_DIR_URL . 'src/js/inlines/upload-video.js', array('jquery', 'dropzone'), PLUGIN_VERSION );

        $ingest_url = rtrim($opencast_options['apiurl'], '/') . '/ingest';
        $seriesid = $opencast_options['seriesid'];
        $workflow = ((isset($opencast_options['uploadworkflow']) && !empty($opencast_options['uploadworkflow'])) ? "{$opencast_options['uploadworkflow']}" : "upload");

        $user_fullname = trim("{$user->user_firstname} {$user->user_lastname}");

        $acceptedFiles = 'video/*';
        $maxFilesize = ((isset($opencast_options['uploadfilesize']) && !empty($opencast_options['uploadfilesize'])) ? $opencast_options['uploadfilesize'] : 256);
        $config = array(
            'dictDefaultMessage' => $text,
            'acceptedFiles' => $acceptedFiles,
            'maxFilesize' => $maxFilesize
        );


        $upload = $this->generate_default_style();
        $upload .= "<div class='oc-upload-box $class'>";
        $upload .= $this->render_lti_form($opencast_options);
        $upload .= "<span class='oc-upload-caption' style='background-image: url(" . plugins_url('/src/images/opencast-white.svg', dirname(__FILE__, 2)) .");background-repeat:no-repeat;background-color:#363636;background-position-x:10px;background-position-y:center;background-attachment:scroll;background-size:70px;background-origin: padding-box;background-clip: border-box;padding:16px 16px 16px 80px;border-radius:10px;text-decoration:none;color:#fff;'></span>";
        $upload .= "<form action='{$ingest_url}' method='post' class='oc-ingest-form' id='ingestForm' enctype='multipart/form-data'>";
        $upload .= "<div class='oc-message' style='display: none; margin: 10px; text-align: center;'></div>";
        $upload .= "<div class='oc-progress'>" . $this->generate_loading_progress() . "</div>";
        $upload .= "<input type='hidden' name='flavor' value='$type/source'>";
        $upload .= "<input type='hidden' name='isPartOf' value='$seriesid'>";
        $upload .= "<input type='hidden' name='workflowId' value='$workflow'>";
        $upload .= "<input type='hidden' name='acl' value='" . $this->create_acl_input() . "'>";
        $upload .= "<input type='text' name='title' placeholder='Title'>";
        $upload .= "<input type='text' name='creator' placeholder='" . __('Author') . "' value='$user_fullname'>";
        $upload .= "<div class='dropzone' data-config='" . json_encode($config) . "' id='ocUpload-" . uniqid() . "'></div>";
        $upload .= "<input type='submit' class='upload-btn' value='$btn_text' data-success='$success_text' data-fail='$fail_text'>";
        $upload .= "</form>";
        $upload .= "</div>";
        
        return $upload;
    }

    private function generate_loading_progress() {
        $loading_content = "<img class='loader-image' src='" .  PLUGIN_DIR_URL . '/src/images/loading.gif' . "'>" ;
        $loading_content .= "<span class='loader-text'>&nbsp" . __('Please wait') . "...&nbsp</span>";
        $loading_content .= "<span class='loader-progress'>0</span>%";
        return $loading_content;
    }

    private function render_lti_form($opencast_options){

        $consumerkey = (isset($opencast_options['consumerkey']) ? $opencast_options['consumerkey'] : '');
        $consumersecret = (isset($opencast_options['consumersecret']) ? $opencast_options['consumersecret'] : '');
        if (!$consumerkey && !$consumersecret) {
            return '';
        }
        $endpoint = $opencast_options['apiurl'] . '/lti';
        $customtools = 'ltitools';
        return LTIConsumer::lti_launch($endpoint, $consumerkey, $consumersecret, $customtools, false);
    }

    private function generate_default_style() {
        return "<style>".
                    file_get_contents(PLUGIN_DIR . 'src/vendors/dropzone/dist/min/dropzone.min.css').
                    file_get_contents(PLUGIN_DIR . 'src/css/inlines/upload-video.css').
                "</style>";
    }

    private function create_acl_input() {
        $lti_roles = LTIConsumer::get_lti_roles();
        $acl_content = file_get_contents(PLUGIN_DIR . "lib/Shortcodes/acl.xml");

        $acl_replaced = str_replace(array_keys($lti_roles), array_values($lti_roles), $acl_content);
        $acl_replaced = str_replace(["\r", "\n"], '', $acl_replaced);
        $acl_replaced = urlencode($acl_replaced);
        return $acl_replaced;
    }
}

?>