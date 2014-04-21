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
    protected $_httpClientOptions = array();

    public function __construct(array $httpClientOptions = array())
    {
        $this->_httpClientOptions = $httpClientOptions;
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

        $request->setUri($url . '/?' . $request->getQuery()->toString())
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
        curl_setopt($ch, CURLOPT_URL, $request->getUriString());

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

        // ensure headers are also returned
        curl_setopt($ch, CURLOPT_HEADER, false);

        // ensure actual response is returned
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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

        $this->_handlers[] = $ch;

        end($this->_handlers);
        return key($this->_handlers);
    }

    public function dispatch()
    {
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
            if (curl_errno($ch)) {
                $this->_responses[$key] = curl_error($ch);
            } else {
                $this->_responses[$key] = curl_multi_getcontent($ch);
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        $this->_handlers = null;

        return $this->_responses;
    }

}
