<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;
use Itscaro\Rest;

class Client extends ClientAbstract {

    /**
     *
     * @var ZendOAuth\Token\Access
     */
    protected $_accessToken;

    /**
     *
     * @var Rest\Client
     */
    protected $_restClient;

    /**
     *
     * @return Rest\Client
     */
    public function getRestClient()
    {
        if ($this->_restClient == null) {
            $restClient = new Rest\Client($this->_optionsHttpClient);
            $this->setRestClient($restClient);
        }

        return $this->_restClient;
    }

    /**
     *
     * @param Rest\Client $restClient
     * @return Client
     */
    public function setRestClient(Rest\Client $restClient)
    {
        $this->_restClient = $restClient;
        return $this;
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
     * @return Flickr\Client
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
        $this->_optionsOAuth->setToken($accessToken);
        return $this;
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
        $defaultParams = array(
            'nojsoncallback' => 1,
            'format' => 'json',
            'method' => $method,
        );
        $finalParams = $this->assembleParams(array_merge($defaultParams, $params));

        $url = $this->getEndpoint() . '/?' . http_build_query($finalParams);

        return $this->getRestClient()->get($url);
    }

    /**
     * Call using HTTP POST
     * @param string $method
     * @param array $params
     * @return object | array
     */
    public function post($method, array $params = array())
    {
        $defaultParams = array(
            'nojsoncallback' => 1,
            'format' => 'json',
            'method' => $method,
        );

        $finalParams = $this->assembleParams(array_merge($defaultParams, $params));

        return $this->getRestClient()->post($this->getEndpoint(), $finalParams);
    }

}
