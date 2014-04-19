<?php

namespace Itscaro\Service;

use ZendOAuth;
use Itscaro\Rest;

class Flickr {

    /**
     *
     * @var ZendOAuth\Http\Utility
     */
    protected $_httpUtility;

    /**
     *
     * @var ZendOAuth\Config\ConfigInterface
     */
    protected $_oauthConfig;

    /**
     *
     * @var ZendOAuth\Token\Access 
     */
    protected $_accessToken;

    function __construct(array $options)
    {
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_oauthConfig = new ZendOAuth\Config\StandardConfig($options);
    }

    /**
     * 
     * @return array
     */
    protected function generateOAuthParams()
    {
        $params = array(
            'oauth_consumer_key' => $this->_oauthConfig->getConsumerKey(),
            'oauth_nonce' => $this->_httpUtility->generateNonce(),
            'oauth_timestamp' => $this->_httpUtility->generateTimestamp(),
            'oauth_signature_method' => $this->_oauthConfig->getSignatureMethod(),
            'oauth_version' => $this->_oauthConfig->getVersion(),
        );

        if ($this->_accessToken instanceof ZendOAuth\Token\Access) {
            $params['oauth_token'] = $this->_accessToken->getParam('oauth_token');
        }
    }

    /**
     * 
     * @param array $params
     * @return object | array
     */
    public function dispatch($method, array $params = array())
    {
        $params['method'] = $method;
        
        $finalParams = array_merge($params, $this->generateOAuthParams());
        $url = $this->getEndpoint() . '/?' . http_build_query($finalParams);

        $client = new Rest\Client();
        return $client->get($url);
    }

}
