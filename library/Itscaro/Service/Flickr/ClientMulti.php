<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;
use ZendOAuth;

class ClientMulti extends ClientAbstract {

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
     * @var Rest\ClientMulti
     */
    protected $_restClient;
    protected $_httpClientOptions;

    public function __construct($endpoint, array $options, array $httpClientOptions)
    {
        parent::__construct($endpoint);
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_oauthConfig = new ZendOAuth\Config\StandardConfig($options);
        $this->_httpClientOptions = $httpClientOptions;

        $this->getRestClient()->setContentType("text/plain");
    }

    /**
     * 
     * @return Rest\ClientMulti
     */
    public function getRestClient()
    {
        if ($this->_restClient == null) {
            $restClient = new Rest\ClientMulti($this->_httpClientOptions);
            $this->setRestClient($restClient);
        }

        return $this->_restClient;
    }

    /**
     * 
     * @param Rest\ClientMulti $restClient
     * @return ClientMulti
     */
    public function setRestClient(Rest\ClientMulti $restClient)
    {
        $this->_restClient = $restClient;
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
     * @return ClientMulti
     */
    public function setAccessToken(ZendOAuth\Token\Access $accessToken)
    {
        $this->_accessToken = $accessToken;
        $this->_oauthConfig->setToken($accessToken);
        return $this;
    }

    /**
     * 
     * @return array
     */
    protected function generateOAuthParams(array $params = array())
    {
        $params = $this->_httpUtility->assembleParams($this->getEndpoint(), $this->_oauthConfig, $params);
        return $params;
        
        $params = array(
            'oauth_consumer_key' => $this->_oauthConfig->getConsumerKey(),
            'oauth_nonce' => $this->_httpUtility->generateNonce(),
            'oauth_timestamp' => $this->_httpUtility->generateTimestamp(),
            'oauth_signature_method' => $this->_oauthConfig->getSignatureMethod(),
            'oauth_version' => $this->_oauthConfig->getVersion(),
        );
        $params['oauth_signature'] = $this->_httpUtility->sign(
            $params,
            $this->_oauthConfig->getSignatureMethod(),
            $this->_oauthConfig->getConsumerSecret(),
            $this->_consumer->getLastRequestToken()->getTokenSecret(),
            $this->_preferredRequestMethod,
            $this->_oauthConfig->getAccessTokenUrl()
        );

        if ($this->_accessToken instanceof ZendOAuth\Token\Access) {
            $params['oauth_token'] = $this->_accessToken->getParam('oauth_token');
        }

        return $params;
    }

    /**
     * Add to queue
     * @param string $httpMethod
     * @param string $method
     * @param array $params
     * @return int Key of the request
     * @throws Exception
     */
    public function addToQueue($httpMethod, $method, array $params = null)
    {
        $defaultParams = array(
            //'api_key' => $this->_oauthConfig->getConsumerKey(),
            'nojsoncallback' => 1,
            'format' => 'json',
            'method' => $method,
        );
        $finalParams = array_merge($defaultParams, $params);

        $finalParams = $this->generateOAuthParams($finalParams);
        //var_dump($this->getEndpoint(), $finalParams);exit;
        switch (strtoupper($httpMethod)) {
            case "GET":
                $result = $this->getRestClient()->get($this->getEndpoint(), $finalParams);
                break;

            case "POST":
                $result = $this->getRestClient()->post($this->getEndpoint(), $finalParams);
                break;
        }

        return $result;
    }

    /**
     * Execute all prepared calls
     * Use self::addToQueue before calling this method
     * @return object | array
     */
    public function dispatchMulti()
    {
        $results = $this->getRestClient()->dispatch();

        return $results;
    }

    /**
     * Execute unique call
     * @param string $httpMethod
     * @param string $method
     * @param array $params
     * @return object | arrays
     */
    public function dispatch($httpMethod, $method, array $params = null)
    {
        $id = $this->addToQueue($httpMethod, $method, $params);
        $result = $this->dispatchMulti();

        return $result[$id];
    }

}
