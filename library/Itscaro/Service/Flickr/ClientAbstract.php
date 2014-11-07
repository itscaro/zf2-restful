<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;
use ZendOAuth;

abstract class ClientAbstract
{

    /**
     *
     * @var ZendOAuth\Http\Utility
     */
    protected $_httpUtility;

    /**
     *
     * @var ZendOAuth\Config\ConfigInterface
     */
    protected $_optionsOAuth;

    /**
     *
     * @var array
     */
    protected $_optionsHttpClient;

    /**
     *
     * @var string
     */
    protected $_endpoint;

    public function __construct($endpoint, array $optionsOAuth, array $optionsHttpClient)
    {
        $this->setEndpoint($endpoint);
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_optionsOAuth = new ZendOAuth\Config\StandardConfig($optionsOAuth);
        $this->_optionsHttpClient = $optionsHttpClient;

        $this->getRestClient()->setContentType("application/json");
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
     * @return ClientAbstract
     */
    public function setEndpoint($endpoint)
    {
        $this->_endpoint = $endpoint;
        return $this;
    }

    /**
     *
     * @param ZendOAuth\Token\Access $accessToken
     * @return \Itscaro\Service\Flickr\Client
     */
    public function setAccessToken(ZendOAuth\Token\Access $accessToken)
    {
        $this->_optionsOAuth->setToken($accessToken);
        return $this;
    }

    /**
     *
     * @return array
     */
    protected function assembleParams(array $params = array())
    {
        $params = $this->_httpUtility->assembleParams($this->getEndpoint(), $this->_optionsOAuth, $params);
        return $params;
    }

}
