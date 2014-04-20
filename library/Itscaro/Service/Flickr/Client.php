<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;
use Itscaro\Rest;

class Client extends ClientAbstract {

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

    public function __construct($endpoint, array $options)
    {
        parent::__construct($endpoint);
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_oauthConfig = new ZendOAuth\Config\StandardConfig($options);

        $this->getRestClient()->setContentType("text/plain");
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
     * @param string $httpMethod
     * @param string $method
     * @param array $params
     * @return object | array
     * @throws Exception
     */
    protected function dispatch($httpMethod, $method, array $params = array())
    {
        $defaultParams = array(
            'nojsoncallback' => 1,
            'format' => 'json',
        );
        $params = array_merge($defaultParams, $params);

        switch (strtoupper($httpMethod)) {
            case "GET":
                $result = $this->get($method, $params);
                break;
            case "POST":
                $result = $this->post($method, $params);
                break;
        }

        if (isset($result['stat']) && $result['stat'] != 'ok') {
            $e = new Exception($result['message'], $result['code']);
            $e->setStat($result['stat']);

            throw $e;
        }

        return $result;
    }

    /**
     * Call using HTTP GET
     * @param string $method
     * @param array $params
     * @return object | array
     */
    public function get($method, array $params = array())
    {
        $params['method'] = $method;

        $finalParams = array_merge($params, $this->generateOAuthParams());
        $url = $this->getEndpoint() . '/?' . http_build_query($finalParams);

        return json_decode($this->getRestClient()->get($url), true);
    }

    /**
     * Call using HTTP POST
     * @param string $method
     * @param array $params
     * @return object | array
     */
    public function post($method, array $params = array())
    {
        $params['method'] = $method;

        $finalParams = array_merge($params, $this->generateOAuthParams());

        return json_decode($this->getRestClient()->post($this->getEndpoint(), $finalParams), true);
    }

}
