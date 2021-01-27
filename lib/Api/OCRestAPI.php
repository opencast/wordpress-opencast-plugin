<?php 

/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api;

class OCRestAPI {
    protected $apiurl,
                $apiusername,
                $apipassword,
                $apitimeout,
                $cookie;

    function __construct() {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $this->apiurl = $opencast_options['apiurl'];
        $this->apiusername = $opencast_options['apiusername'];
        $this->apipassword = $opencast_options['apipassword'];
        $this->apitimeout = ((isset($opencast_options['apitimeout']) && !empty(isset($opencast_options['apitimeout']))) ? $opencast_options['apitimeout'] : 1);
    }

    public function setUrl($endpoint) {
        $this->apiurl = $endpoint;
    }

    public function oc_get($service_url) {
        $args['method'] = 'GET';
        return $this->remote_request($service_url, $args);
    }

    public function oc_post($service_url, $form_fields) {
        $args['method'] = 'POST';
        if (!empty($form_fields)) {
            $args['body'] = $form_fields;
        }
        return $this->remote_request($service_url, $args, 'multipart/form-data');
    }

    public function oc_put($service_url, $form_fields) {
        
        $args['method'] = 'PUT';
        if (!empty($form_fields)) {
            $args['body'] = $form_fields;
        }
        return $this->remote_request($service_url, $args, 'multipart/form-data');
    }

    public function oc_delete($service_url, $data = array()) {
        $args['method'] = 'DELETE';
        return $this->remote_request($service_url, $args);
    }

    private function remote_request($service_url, $args = array(), $content_type = '') {

        if (!function_exists('wp_remote_request')) {
            return false;
        }

        if (!$this->validate_params($service_url, $args)) {
            return false;
        }

        $url = '';
        if (!is_array($service_url)) {
            $url = rtrim($this->apiurl, '/') . '/'. ltrim($service_url, '/');
        } else {
            $url = rtrim($service_url[0], '/') . '/'. ltrim($service_url[1], '/');
        }
        
        $args = $this->prepareArgs($args, $content_type);

        $res = wp_remote_request($url, $args);

        if (is_wp_error($res)) {
            return false;
        }

        if ($res['response']['code'] == 204 && $args['method'] == 'DELETE') {
            return true;
        }

        $body_array = json_decode( wp_remote_retrieve_body($res), true );

        //Check for success
        if( is_array( $body_array ) && ($res['response']['code'] == 200 || $res['response']['code'] == 201) ) {
            return $body_array;
        }
        
        return false;
    }

    private function validate_params($service_url, $args) {
        return (!empty($service_url) && !empty($this->apiurl) && 
                !empty($this->apiusername) &&  !empty($this->apipassword) &&
                (isset($args['method']) && (in_array($args['method'], array('GET', 'POST', 'PUT', 'DELETE')))));
    }

    private function prepareArgs($args, $content_type)
    {
        $header = array();
        $basicauth = base64_encode("{$this->apiusername}:{$this->apipassword}");
        $header['Authorization'] = "Basic $basicauth";
        $header['X-Requested-Auth'] = "Digest";
        (!$content_type) ?: $header['Content-Type'] = "$content_type";
        $args['headers'] = $header;
        $args['timeout'] = $this->apitimeout;
        $args['sslverify'] = false;

        return $args;
    }
}