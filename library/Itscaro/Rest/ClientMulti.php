<?php

namespace Itscaro\Rest;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class ClientMulti extends Client {

    protected $_handlers = array();
    protected $_responses = array();
    protected $_httpClientOptions = array();

    public function __construct(array $httpClientOptions = array())
    {
        $this->_httpClientOptions = $httpClientOptions;
    }

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

        $client = new HttpClient('', $this->_httpClientOptions);
        $adapter = $client->getAdapter();
        /* @var $adapter \Zend\Http\Client\Adapter\Curl */
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
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "PUT";
                break;

            case 'PATCH' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "PATCH";
                break;

            case 'DELETE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "DELETE";
                break;

            case 'OPTIONS' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "OPTIONS";
                break;

            case 'TRACE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "TRACE";
                break;

            case 'HEAD' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "HEAD";
                break;

            default:
                // For now, through an exception for unsupported request methods
                throw new \Exception("Method '$method' currently not supported");
        }

        // mark as HTTP request and set HTTP method
        curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($ch, $curlMethod, $curlValue);

        // ensure headers are also returned
        curl_setopt($ch, CURLOPT_HEADER, true);

        // ensure actual response is returned
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Treating basic auth headers in a special way
        if ($request->getHeaders() instanceof \Zend\Http\Headers) {
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
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getPost()->toArray());
        } elseif ($method == 'PUT') {
            // This is a PUT by a setRawData string, not by file-handle
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getPost()->toArray());
        } elseif ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getPost()->toArray());
        }

        $this->_handlers[] = $ch;
    }

    protected function dispatch()
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
            $this->_reponses[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        $this->_handlers = null;

        return $this->_reponses;
    }

}
