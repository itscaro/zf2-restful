<?php

namespace Itscaro\Rest;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class Client
{

    const HTTP_VERB_GET = 'GET';
    const HTTP_VERB_POST = 'POST';
    const HTTP_VERB_PUT = 'PUT';
    const HTTP_VERB_PATCH = 'PATCH';
    const HTTP_VERB_DELETE = 'DELETE';
    const HTTP_VERB_OPTIONS = 'OPTIONS';
    const HTTP_VERB_HEAD = 'HEAD';

    protected $_httpClientOptions = array();

    public function __construct(array $httpClientOptions = array())
    {
        $this->_httpClientOptions = $httpClientOptions;
    }

    /**
     *
     * @var HttpClient
     */
    protected $_httpClient;

    /**
     *
     * @var string
     */
    protected $_contentType = "application/json";

    /**
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        if ($this->_httpClient == null) {
            $httpClient = new HttpClient('', $this->_httpClientOptions);
            $this->setHttpClient($httpClient);
        }
        return $this->_httpClient;
    }

    /**
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->_httpClient = $httpClient;
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

    public function get($url, array $query = null)
    {
        return $this->execute($url, self::HTTP_VERB_GET, $query);
    }

    public function post($url, array $query = null, array $rawdata = null)
    {
        return $this->execute($url, self::HTTP_VERB_POST, $query, $rawdata);
    }

    public function put($url, array $query = null, array $rawdata = null)
    {
        return $this->execute($url, self::HTTP_VERB_PUT, $query, $rawdata);
    }

    public function patch($url, array $query = null, array $rawdata = null)
    {
        return $this->execute($url, self::HTTP_VERB_PATCH, $query, $rawdata);
    }

    public function delete($url, array $query = null)
    {
        return $this->execute($url, self::HTTP_VERB_DELETE, $query);
    }

    public function options($url, array $query = null)
    {
        return $this->execute($url, self::HTTP_VERB_OPTIONS, $query);
    }

    public function head($url, array $query = null)
    {
        return $this->execute($url, self::HTTP_VERB_HEAD, $query);
    }

    /**
     *
     * @param string $url
     * @param string $method
     * @param array $query
     * @return object | array
     */
    protected function execute($url, $method, array $query = null, array $rawdata = null, \Zend\Http\Headers $headers = null)
    {
        $request = new Request();
        $this->getHttpClient()->setRequest($request);
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => $this->getContentType()
        ));
        $request->setUri($url)
            ->setMethod($method);
        if ($query) {
            $request->setQuery(new Parameters($query));
        }
        if ($headers) {
            $request->setHeaders($headers);
        }

        switch ($method) {
            case self::HTTP_VERB_POST:
            case self::HTTP_VERB_PUT:
            case self::HTTP_VERB_PATCH:
                if ($rawdata) {
                    $request->setPost(new Parameters($rawdata));
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
