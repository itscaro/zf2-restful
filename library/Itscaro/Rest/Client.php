<?php

namespace Itscaro\Rest;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
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
     * @var HttpClient
     */
    protected static $_httpClient;

    /**
     *
     * @var string 
     */
    protected $_contentType = "application/json";

    /**
     * 
     * @return HttpClient
     */
    public static function getHttpClient()
    {
        if (static::$_httpClient == null) {
            $httpClient = new HttpClient();
            static::setHttpClient($httpClient);
        }
        return static::$_httpClient;
    }

    /**
     * 
     * @param HttpClient $httpClient
     */
    public static function setHttpClient(HttpClient $httpClient)
    {
        static::$_httpClient = $httpClient;
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
    public function setContentType($contentType)
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

    /**
     * 
     * @param string $url
     * @param string $method
     * @param array $data
     * @return object | array
     */
    protected function execute($url, $method, array $data = array(), \Zend\Http\Headers $headers = null)
    {
        $request = new Request();
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => $this->getContentType()
        ));
        $request->setUri($url);
        $request->setMethod($method);
        if ($headers) {
            $request->setHeaders($headers);
        }
        
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
     * @param Response $response
     */
    protected function _processStatus(Response $response)
    {
        
    }

    /**
     * 
     * @param Response $response
     * @return object | array
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
