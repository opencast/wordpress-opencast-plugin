<?php 

/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api\OAuth;
use Opencast\Api\OAuth\OAuthConsumer;
use Opencast\Api\OAuth\OAuthToken;

/**
 * A Trivial memory-based store - no support for tokens.
 * 
 * Pulled from Moodle code base
 */
class TrivialOAuthDataStore extends OAuthDataStore {

    /** @var array $consumers  Array of tool consumer keys and secrets */
    private $consumers = array();

    /**
     * Add a consumer to the array
     *
     * @param string $consumerkey     Consumer key
     * @param string $consumersecret  Consumer secret
     */
    public function add_consumer( $consumerkey, $consumersecret ) {
        $this->consumers[ $consumerkey ] = $consumersecret;
    }

    /**
     * Get OAuth consumer given its key
     *
     * @param string $consumerkey     Consumer key
     *
     * @return \OAuthConsumer  OAuthConsumer object
     */
    public function lookup_consumer( $consumerkey ) {
        if ( strpos( $consumerkey, "http://" ) === 0 ) {
            $consumer = new OAuthConsumer( $consumerkey, "secret", null );
            return $consumer;
        }
        if ( isset($this->consumers[ $consumerkey ] ) ) {
            $consumer = new OAuthConsumer( $consumerkey, $this->consumers[ $consumerkey ], null );
            return $consumer;
        }
        return null;
    }

    /**
     * Create a dummy OAuthToken object for a consumer
     *
     * @param \OAuthConsumer $consumer     Consumer
     * @param string $tokentype    Type of token
     * @param string $token        Token ID
     *
     * @return \OAuthToken OAuthToken object
     */
    public function lookup_token( $consumer, $tokentype, $token ) {
        return new OAuthToken( $consumer, '' );
    }

    /**
     * Nonce values are not checked so just return a null
     *
     * @param \OAuthConsumer $consumer     Consumer
     * @param string $token        Token ID
     * @param string $nonce        Nonce value
     * @param string $timestamp    Timestamp
     *
     * @return null
     */
    public function lookup_nonce( $consumer, $token, $nonce, $timestamp ) {
        // Should add some clever logic to keep nonces from
        // being reused - for now we are really trusting
        // that the timestamp will save us.
        return null;
    }

    /**
     * Tokens are not used so just return a null.
     *
     * @param \OAuthConsumer $consumer     Consumer
     *
     * @return null
     */
    public function new_request_token( $consumer ) {
        return null;
    }

    /**
     * Tokens are not used so just return a null.
     *
     * @param string $token        Token ID
     * @param \OAuthConsumer $consumer     Consumer
     *
     * @return null
     */
    public function new_access_token( $token, $consumer ) {
        return null;
    }

}