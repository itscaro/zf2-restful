<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;
use ZendOAuth;

class ClientMulti extends ClientAbstract {

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

    /**
     *
     * @return Rest\ClientMulti
     */
    public function getRestClient()
    {
        if ($this->_restClient == null) {
            $restClient = new Rest\ClientMulti($this->_optionsHttpClient);
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
        $this->_optionsOAuth->setToken($accessToken);
        return $this;
    }

    /**
     * Add to queue
     * @param string $httpMethod
     * @param string $method
     * @param array $params
     * @return int Key of the request
     * @throws Exception
     */
    public function addToQueue($httpMethod, $method, array $params = array())
    {
        $defaultParams = array(
            //'api_key' => $this->_oauthConfig->getConsumerKey(),
            'nojsoncallback' => 1,
            'format' => 'json',
            'method' => $method,
        );
        $finalParams = $this->assembleParams(array_merge($defaultParams, $params));

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
     * @return object|array
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
     * @return object|array
     */
    public function dispatch($httpMethod, $method, array $params = null)
    {
        $id = $this->addToQueue($httpMethod, $method, $params);
        $result = $this->dispatchMulti();

        return $result[$id];
    }

}
