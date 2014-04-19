<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;
use Itscaro\Rest;

class Client {

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

    /**
     *
     * @var string
     */
    protected $_endpoint;

    function __construct($endpoint, array $options)
    {
        $this->setEndpoint($endpoint);
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_oauthConfig = new ZendOAuth\Config\StandardConfig($options);
    }

    /**
     * 
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        return Rest\Client::getHttpClient();
    }

    /**
     * 
     * @param \Zend\Http\Client $httpClient
     * @return \Itscaro\Service\Flickr\Client
     */
    public function setHttpClient(\Zend\Http\Client $httpClient)
    {
        Rest\Client::setHttpClient($httpClient);

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * 
     * @param string $endpoint
     * @return \Itscaro\Service\Flickr\Client
     */
    public function setEndpoint($endpoint)
    {
        $this->_endpoint = $endpoint;
        return $this;
    }

    /**
     * 
     * @return ZendOAuth\Token\Access
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * 
     * @param ZendOAuth\Token\Access $accessToken
     * @return \Itscaro\Service\Flickr\Client
     */
    public function setAccessToken(ZendOAuth\Token\Access $accessToken)
    {
        $this->_accessToken = $accessToken;
        return $this;
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
        
        return $params;
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
