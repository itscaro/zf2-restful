<?php

namespace Itscaro\Rest;

use Exception;
use Itscaro\Rest\Client as RestClient;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class ClientMulti extends RestClient {

    protected $_handlers = array();
    protected $_responses = array();
    protected $_infos = array();
    protected $_httpClientOptions = array();

    /**
     *
     * @var \Closure
     */
    protected $_callbackWriteFunction;

    /**
     *
     * @var \Closure 
     */
    protected $_callbackHeaderFunction;

    public function __construct(array $httpClientOptions = array())
    {
        $this->_httpClientOptions = $httpClientOptions;
    }

    /**
     * 
     * @return resource[]
     * @throws Exception
     */
    public function getHandlers()
    {
        return $this->_handlers;
    }

    /**
     * 
     * @param string $id
     * @return resource curl resource
     * @throws Exception
     */
    public function getHandler($id)
    {
        if (isset($this->_handlers[$id])) {
            return $this->_handlers[$id];
        } else {
            throw new Exception("There is no handler with the id '{$id}'");
        }
    }

    /**
     * 
     * @return array
     */
    public function getResponses()
    {
        return $this->_responses;
    }

    /**
     * 
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function getResponse($id)
    {
        if (isset($this->_responses[$id])) {
            return $this->_responses[$id];
        } else {
            throw new Exception("There is no response with the id '{$id}'");
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getInfos()
    {
        return $this->_infos;
    }

    /**
     * 
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function getInfo($id)
    {
        if (isset($this->_infos[$id])) {
            return $this->_infos[$id];
        } else {
            throw new Exception("There is no infos with the id '{$id}'");
        }
    }

    public function getCallbackWriteFunction()
    {
        return $this->_callbackWriteFunction;
    }

    public function setCallbackWriteFunction(\Closure $callbackWriteFunction)
    {
        $this->_callbackWriteFunction = $callbackWriteFunction;
        return $this;
    }

    public function removeCallbackWriteFunction()
    {
        $this->_callbackWriteFunction = null;
        return $this;
    }

    public function getCallbackHeaderFunction()
    {
        return $this->_callbackHeaderFunction;
    }

    public function setCallbackHeaderFunction(\Closure $callbackHeaderFunction)
    {
        $this->_callbackHeaderFunction = $callbackHeaderFunction;
        return $this;
    }

    public function removeCallbackHeaderFunction()
    {
        $this->_callbackHeaderFunction = null;
        return $this;
    }

    public function reset() {
        $this->_callbackHeaderFunction = null;
        $this->_callbackWriteFunction = null;
        $this->_handlers = array();
        $this->_responses = array();
        $this->_infos = array();
    }
    
    protected function execute($url, $method, array $query = null, array $rawdata = null, Headers $headers = null)
    {
        $request = new Request();

        // Headers
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => $this->getContentType()
        ));
        if ($headers) {
            $request->getHeaders()->addHeaders($headers);
        }

        // Query
        if ($query) {
            $request->setQuery(new Parameters($query));
        }

        $request->setUri($url)
                ->setMethod($method);

        switch ($method) {
            case self::HTTP_VERB_POST:
            case self::HTTP_VERB_PUT:
            case self::HTTP_VERB_PATCH:
                if ($rawdata) {
                    $request->setPost(new Parameters($rawdata));
                }
                break;
        }

        $client = new HttpClient('', $this->_httpClientOptions);
        $adapter = $client->getAdapter();
        /* @var $adapter Curl */
        $secure = $request->getUri()->getScheme() == 'https';
        $adapter->connect($request->getUri()->getHost(), $request->getUri()->getPort(), $secure);
        $ch = $adapter->getHandle();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $request->getUriString() . '?' . $request->getQuery()->toString());

        // ensure correct curl call
        $curlValue = true;
        switch ($method) {
            case 'GET' :
                $curlMethod = CURLOPT_HTTPGET;
                break;

            case 'POST' :
                $curlMethod = CURLOPT_POST;
                break;

            case 'PUT' :
            case 'PATCH' :
            case 'DELETE' :
            case 'OPTIONS' :
            case 'TRACE' :
            case 'HEAD' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = $method;
                break;

            default:
                // For now, through an exception for unsupported request methods
                throw new Exception("Method '$method' currently not supported");
        }

        // mark as HTTP request and set HTTP method
        curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($ch, $curlMethod, $curlValue);

        // ensure actual response is returned
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set callback function
        if ($this->getCallbackWriteFunction() instanceof \Closure) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, $this->getCallbackWriteFunction());
        }

        // ensure headers are also returned
        curl_setopt($ch, CURLOPT_HEADER, false);

        // set callback function
        if ($this->getCallbackHeaderFunction() instanceof \Closure) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, $this->getCallbackHeaderFunction());
        }

        // Treating basic auth headers in a special way
        if ($request->getHeaders() instanceof Headers) {
            $headersArray = $request->getHeaders()->toArray();
            if (array_key_exists('Authorization', $headersArray) && 'Basic' == substr($headersArray['Authorization'], 0, 5)) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, base64_decode(substr($headersArray['Authorization'], 6)));
                unset($headersArray['Authorization']);
            }

            // set additional headers
            if (!isset($headersArray['Accept'])) {
                $headersArray['Accept'] = '';
            }
            $curlHeaders = array();
            foreach ($headersArray as $key => $value) {
                $curlHeaders[] = $key . ': ' . $value;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        // POST body
        switch ($method) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getPost()->toArray());
                break;
        }

        $this->_handlers[uniqid()] = $ch;

        end($this->_handlers);
        return key($this->_handlers);
    }

    public function dispatch()
    {
        // Reset response and infos before a new dispatch
        unset($this->_responses);
        $this->_responses = array();
        unset($this->_infos);
        $this->_infos = array();

        //create the multiple cURL handle
        $mh = curl_multi_init();

        //add the two handles
        foreach ($this->_handlers as $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        //close the handles
        foreach ($this->_handlers as $key => $ch) {
            $this->_infos[$key] = curl_getinfo($ch);
            if (curl_errno($ch)) {
                $this->_responses[$key] = curl_error($ch);
            } else {
                $this->_responses[$key] = curl_multi_getcontent($ch);
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        // Reset handlers after dispatching
        unset($this->_handlers);
        $this->_handlers = array();

        return $this->_responses;
    }
    
}
