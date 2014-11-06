<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;

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
    protected $_oauthConfig;

    /**
     *
     * @var string
     */
    protected $_endpoint;

    function __construct($endpoint)
    {
        $this->setEndpoint($endpoint);
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
        $this->_oauthConfig->setToken($accessToken);
        return $this;
    }

    /**
     *
     * @return array
     */
    protected function assembleParams(array $params = array())
    {
        $params = $this->_httpUtility->assembleParams($this->getEndpoint(), $this->_oauthConfig, $params);
        return $params;
    }

}
