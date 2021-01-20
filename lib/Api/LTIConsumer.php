<?php 

/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api;

use Opencast\Api\OAuth\OAuthConsumer;
use Opencast\Api\OAuth\OAuthRequest;
use Opencast\Api\OAuth\OAuthSignatureMethod_HMAC_SHA1;

define('INSTRUCTOR_ROLE', 'Instructor');
define('INSTRUCTOR_USERID', '1');
define('LEARNER_ROLE', 'Learner');
define('LEARNER_USERID', '2');

class LTIConsumer
{
    public static function lti_launch($endpoint, $consumerkey, $consumersecret, $customtools,  $auto_submit = false, $extra_roles = array()) {
        $launch_url = html_entity_decode( urldecode( $endpoint ) );
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $current_user = wp_get_current_user();
        $user_roles = ( array ) $current_user->roles;
        // ext_user_username
        // lis_person_contact_email_primary
        // lis_person_sourcedid
  
        //roles & users
        $launch_data = self::set_lti_launch_user($opencast_options, $user_roles, $extra_roles);

        $launch_data = $launch_data + array(
            //extra user
            'lis_person_name_given' => $current_user->user_firstname,
            'lis_person_name_family' => $current_user->user_lastname,
            'lis_person_name_full' => $current_user->user_firstname . ' ' . $current_user->user_lastname,
            'ext_user_username' => ($current_user->user_login) ? $current_user->user_login : 'wordpress',
            'lis_person_contact_email_primary' => ($current_user->user_email) ? ($current_user->user_email) : 'wordpress@wordpress.org',
            'lis_person_sourcedid' => ($current_user->user_login) ? $current_user->user_login : 'wordpress',

            //lti setting
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',


            //OAuth
            'oauth_nonce' => md5( uniqid( '', true ) ),
            'oauth_consumer_key' => $consumerkey,
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
            'oauth_callback' => 'about:blank',

            //Custom
            'custom_tool' =>  $customtools,

            'context_id' => home_url(),
            'context_title' =>  get_bloginfo( 'name' ),
            'context_label_name' => 'WP',
            'resource_link_id' => 'o' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
            'resource_link_title' => 'Opencast',
            'context_type' => 'CourseSearch',

            'launch_presentation_locale' => get_locale(),
            'tool_consumer_info_product_family_code' => 'wordpress',
            'tool_consumer_info_version' => get_bloginfo( 'version' ),
            'tool_consumer_instance_name' => get_bloginfo( 'name' ),
            'tool_consumer_instance_url' => home_url(),
            'tool_consumer_instance_guid' => $_SERVER['SERVER_NAME'] . '_wordpress',
            'launch_presentation_document_target' => 'iframe',
        );
        
        $consumer = new OAuthConsumer( $consumerkey, $consumersecret, 'about:blank' );
        $oauth_request = OAuthRequest::from_consumer_and_token(
            $consumer, null, 'POST', $launch_url, $launch_data );
        $oauth_request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, null );
        $params = $oauth_request->get_parameters();
        
        return self::render_lti_form($endpoint, $params, $auto_submit);
    }


    private static function set_lti_launch_user($opencast_options, $user_roles, $extra_roles) {
        $launch_data = array();
        $instructors = ((isset($opencast_options['ltiinstructors'])) ? $opencast_options['ltiinstructors'] : array("administrator"));
        if (array_intersect($user_roles, $instructors)) {
            $launch_data['roles'] = INSTRUCTOR_ROLE;
            $launch_data['user_id'] = INSTRUCTOR_USERID;
        } else {
            $launch_data['roles'] = LEARNER_ROLE;
            $launch_data['user_id'] = LEARNER_USERID;
        }
        if (!empty($extra_roles)) {
            $launch_data['roles'] = $launch_data['roles'] . ',' . implode(',', $extra_roles);
        }
        return $launch_data;
    }

    public static function get_lti_roles() {
        return array(
            'ROLE_USER_LTI_Instructor' => home_url() . "_" . INSTRUCTOR_ROLE,
            'ROLE_USER_LTI_Learner' => home_url() . "_" . LEARNER_ROLE,
        );
    }

    /**
     * Display the lti form.
     *
     * @param object $data The prepared variables.
     * @return string
     */
    public static function render_lti_form($endpoint, $params, $auto_submit) {
        $name = esc_attr("OCLtiLaunchForm" . ($auto_submit ? 'Auto' : '')) ;
        $identifier = ($auto_submit ? "id='$name'" : "name='$name'");
        $content = '';
        $content .= $auto_submit ?  '<script>'.
                        'window.addEventListener("load", function() {document.getElementById("'. esc_js($name) .'").submit();})'
                    .'</script>' : '';
        $content .= "<form action='$endpoint' $identifier " .
                        "method='post' encType='application/x-www-form-urlencoded'>\n";

        // Construct html form for the launch parameters.
        foreach ($params as $key => $value) {
            $key = esc_attr(htmlspecialchars($key));
            $value = esc_attr(htmlspecialchars($value));
            $content .= "<input type='hidden' name='$key' value='$value' >";
        }
        $content .= "</form>\n";

        return $content;
    }
}
?>