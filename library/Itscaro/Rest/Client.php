<?php

namespace Itscaro\Rest;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

class Client {

    const HTTP_VERB_GET = 'GET';
    const HTTP_VERB_POST = 'POST';
    const HTTP_VERB_PUT = 'PUT';
    const HTTP_VERB_PATCH = 'PATCH';
    const HTTP_VERB_DELETE = 'DELETE';
    const HTTP_VERB_OPTIONS = 'OPTIONS';
    const HTTP_VERB_HEAD = 'HEAD';

    public function __construct()
    {
        
    }

    /**
     *
     * @var Zend\Http\Client
     */
    protected $_httpClient;

    /**
     *
     * @var type 
     */
    protected $_contentType = "application/json";

    /**
     * 
     * @return Zend\Http\Client
     */
    public function getHttpClient()
    {
        if ($this->_httpClient == null) {
            $httpClient = new HttpClient();
            $this->setHttpClient($httpClient);
        }
        return $this->_httpClient;
    }

    /**
     * 
     * @param Zend\Http\Client $httpClient
     * @return \Itscaro\Rest\Client
     */
    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * 
     * @param string $contentType
     * @return \Itscaro\Rest\Client
     */
    public function setContentType(type $contentType)
    {
        $this->_contentType = $contentType;
        return $this;
    }

    public function get($url)
    {
        return $this->execute($url, self::HTTP_VERB_GET);
    }

    public function post($url, $data)
    {
        return $this->execute($url, self::HTTP_VERB_POST, $data);
    }

    public function put($url, $data)
    {
        return $this->execute($url, self::HTTP_VERB_PUT, $data);
    }

    public function patch($url, $data)
    {
        return $this->execute($url, self::HTTP_VERB_PATCH, $data);
    }

    public function delete($url)
    {
        return $this->execute($url, self::HTTP_VERB_DELETE);
    }

    public function options($url)
    {
        return $this->execute($url, self::HTTP_VERB_OPTIONS);
    }

    public function head($url)
    {
        return $this->execute($url, self::HTTP_VERB_HEAD);
    }

    protected function execute($url, $method, $data = null)
    {
        $request = new Request();
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => $this->getContentType()
        ));
        $request->setUri($url);
        $request->setMethod($method);

        switch ($method) {
            case self::HTTP_VERB_POST:
            case self::HTTP_VERB_PUT:
            case self::HTTP_VERB_PATCH:
                if ($data) {
                    $request->setPost(new Parameters($data));
                }
                break;

            default:
                break;
        }

        $response = $this->getHttpClient()->dispatch($request);

        $this->_processStatus($response);

        return $this->_processResponse($response);
    }

    /**
     * 
     * @param \Itscaro\Rest\Response $response
     */
    protected function processStatus(Response $response)
    {
        
    }

    /**
     * 
     * @param \Itscaro\Rest\Response $response
     * @return type
     */
    protected function _processResponse(Response $response)
    {
        switch ($this->getContentType()) {
            case 'application/json':
                return json_decode($response->getBody(), true);

            default:
                return $response->getBody();
        }
    }

}
