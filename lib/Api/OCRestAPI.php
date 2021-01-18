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
        $options[CURLOPT_HTTPGET] = 1;
        return $this->remote_request($service_url, $options);
    }

    public function oc_post($service_url, $form_fields) {
        $options[CURLOPT_POST] = 1;
        if (!empty($form_fields)) {
            $options[CURLOPT_POSTFIELDS] = $form_fields;
        }
        return $this->remote_request($service_url, $options, 'multipart/form-data');
    }

    public function oc_put($service_url, $form_fields) {
        
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        if (!empty($form_fields)) {
            $options[CURLOPT_POSTFIELDS] = $form_fields;
        }
        return $this->remote_request($service_url, $options, 'multipart/form-data');
    }

    public function oc_delete($service_url, $data = array()) {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        return $this->remote_request($service_url, $options);
    }

    private function remote_request($service_url, $options = array(), $content_type = ''/* 'application/json' */) {

        if (!function_exists('curl_init')) {
            return false;
        }

        if (!$this->validate_params($service_url, $options)) {
            return false;
        }

        $url = '';
        if (!is_array($service_url)) {
            $url = rtrim($this->apiurl, '/') . '/'. ltrim($service_url, '/');
        } else {
            $url = rtrim($service_url[0], '/') . '/'. ltrim($service_url[1], '/');
        }
        
        $ch = $this->initCurl($content_type);

        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_FRESH_CONNECT] = 1;
        curl_setopt_array($ch , $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            return false;
        } 

        if ($httpCode == 204 && $options[CURLOPT_CUSTOMREQUEST] == 'DELETE') {
            return true;
        }

        $responseResult = json_decode( $response, true );
        if ( is_array( $responseResult ) && ! is_wp_error( $responseResult ) ) {
            return $responseResult;
        } else {
            return false;
        }

        return true;
    }

    private function validate_params($service_url, $options) {
        return (!empty($service_url) && !empty($this->apiurl) && 
                !empty($this->apiusername) &&  !empty($this->apipassword) &&
                (isset($options[CURLOPT_POST]) || isset($options[CURLOPT_HTTPGET]) || isset($options[CURLOPT_CUSTOMREQUEST])));
    }

    private function initCurl($content_type)
    {
        // setting up a curl-handler
        $ch = curl_init();
        $header = array();
        $basicauth = base64_encode("{$this->apiusername}:{$this->apipassword}");
        $header[] = "Authorization: Basic $basicauth";
        $header[] = "X-Requested-Auth: Digest";
        (!$content_type) ?: $header[] = "Content-Type: $content_type";
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->apitimeout);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        //ssl
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);

        return $ch;
    }
}