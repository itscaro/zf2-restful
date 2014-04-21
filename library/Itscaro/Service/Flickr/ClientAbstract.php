<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;

abstract class ClientAbstract {

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
    
}
