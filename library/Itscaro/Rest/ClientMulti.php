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

    protected function execute($url, $method, array $data = array())
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

        $client = new HttpClient('', $this->_httpClientOptions);
        $adapter = $client->getAdapter();
        /* @var $adapter \Zend\Http\Client\Adapter\Curl */
        $secure = $request->getUri()->getScheme() == 'https';
        $adapter->connect($request->getUri()->getHost(), $request->getUri()->getPort(), $secure);
        $ch = $adapter->getHandle();

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
