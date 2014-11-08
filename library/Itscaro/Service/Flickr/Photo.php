<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;
use Zend\Stdlib\Parameters;

/**
 * Wrapper
 *
 * @author Minh-Quan
 */
class Photo {

    const ERR_RESPONSE_NOT_XML = 10000;
    
    /**
     *
     * @var \Zend\Http\Client
     */
    protected $_httpClient;

    /**
     *
     * @var string
     */
    protected $_endpointUpload = 'https://up.flickr.com/services/upload/';

    /**
     *
     * @var string
     */
    protected $_endpointReplace = 'https://up.flickr.com/services/replace/';

    /**
     * Set to 0 for no, 1 for yes. Specifies who can view the photo.
     * @var array
     */
    protected $_visibility = array(
        'is_public' => 0,
        'is_friend' => 0,
        'is_family' => 0,
    );

    /**
     * Set to 1 to keep the photo in global search results, 2 to hide from public searches.
     * @var int
     */
    protected $_hidden = array(
        'hidden' => 1
    );

    /**
     * Set to 1 for Safe, 2 for Moderate, or 3 for Restricted.
     * @var int
     */
    protected $_safetyLevel = array(
        'safety_level' => 1
    );
    protected $_configOAuth;

    public function __construct(ZendOAuth\Token\Access $accessToken, array $optionsOAuth = array(), array $optionsHttpClient = array())
    {
        $this->_httpClient = new \Zend\Http\Client('', $optionsHttpClient);
        $this->_httpUtility = new ZendOAuth\Http\Utility();
        $this->_configOAuth = new ZendOAuth\Config\StandardConfig($optionsOAuth);
        $this->_configOAuth->setToken($accessToken);
    }

    /**
     * Set visibility, default to private
     * @param type $isPublic
     * @param type $isFriend
     * @param type $isFamily
     */
    public function setVisibility($isPublic = 0, $isFriend = 0, $isFamily = 0)
    {
        $this->_visibility = array(
            'is_public' => $isPublic,
            'is_friend' => $isFriend,
            'is_family' => $isFamily,
        );
    }

    /**
     *
     * @param type $hidden
     */
    public function setHidden($hidden = false)
    {
        $this->_hidden = array(
            'hidden' => ($hidden ? 2 : 1)
        );
    }

    /**
     *
     * @param type $safetyLevel
     */
    public function setSafetyLevel($safetyLevel = 1)
    {
        if (!in_array($safetyLevel, array(1, 2, 3))) {
            throw new \Exception('Safety Level can be 1, 2 or 3');
        }

        $this->_safetyLevel = array(
            'safety_level' => $safetyLevel
        );
    }

    /**
     *
     * @param type $filePath
     * @param type $title
     * @param type $description
     * @param type $tags
     * @param type $contentType
     * @param type $async
     * @return type
     * @throws \Exception
     */
    protected function _upload($filePath, $title = null, $description = null, $tags = null, $contentType = 1, $async = 0)
    {
        if (!in_array($contentType, array(1, 2, 3))) {
            throw new \Exception('Content Type can be 1, 2 or 3');
        }

        $params = array_merge($this->_visibility, $this->_hidden, $this->_safetyLevel);
        $params['async'] = $async;
        $params['content_type'] = $contentType;
        if (!is_null($title)) {
            $params['title'] = $title;
        }
        if (!is_null($description)) {
            $params['description'] = $description;
        }
        if (!is_null($tags)) {
            $params['tags'] = $tags;
        }

        $finalParams = $this->_httpUtility->assembleParams($this->_endpointUpload, $this->_configOAuth, $params);

        $request = new \Zend\Http\Request();
        $request->setUri($this->_endpointUpload)
                ->setMethod('POST')
                ->setPost(new Parameters($finalParams));

        $this->_httpClient->reset();
        $this->_httpClient->setRequest($request);
        $this->_httpClient->setEncType(\Zend\Http\Client::ENC_FORMDATA, 'ITSCARO');
        $this->_httpClient->setFileUpload($filePath, 'photo');

        $response = $this->_httpClient->dispatch($request);

        $decodedResponse = @simplexml_load_string($response->getBody());

        if (!$decodedResponse instanceof \SimpleXMLElement) {
            throw new \Exception('Could not decode response: ' . $response->getBody(), self::ERR_RESPONSE_NOT_XML);
        } else {
            if ($decodedResponse['stat'] == 'ok') {
                if ($async) {
                    return (string) $decodedResponse->ticketid;
                } else {
                    return (string) $decodedResponse->photoid;
                }
            } else {
                throw new \Exception((string) $decodedResponse->err['msg'], (int) $decodedResponse->err['code']);
            }
        }
    }

    protected function _replace($filePath, $photoId, $async = 0)
    {
        $params['async'] = $async;
        $params['photo_id'] = $photoId;

        $finalParams = $this->_httpUtility->assembleParams($this->_endpointReplace, $this->_configOAuth, $params);

        $request = new \Zend\Http\Request();
        $request->setUri($this->_endpointReplace)
                ->setMethod('POST')
                ->setPost(new Parameters($finalParams));

        $this->_httpClient->reset();
        $this->_httpClient->setRequest($request);
        $this->_httpClient->setEncType(\Zend\Http\Client::ENC_FORMDATA, 'ITSCARO');
        $this->_httpClient->setFileUpload($filePath, 'photo');

        $response = $this->_httpClient->dispatch($request);

        $decodedResponse = simplexml_load_string($response->getBody());

        if (!$decodedResponse instanceof \SimpleXMLElement) {
            throw new \Exception('Could not decode response: ' . $response->getBody(), self::ERR_RESPONSE_NOT_XML);
        } else {
            if ($decodedResponse['stat'] == 'ok') {
                if ($async) {
                    return (string) $decodedResponse->ticketid;
                } else {
                    return (string) $decodedResponse->photoid;
                }
            } else {
                throw new \Exception((string) $decodedResponse->err['msg'], (int) $decodedResponse->err['code']);
            }
        }
    }

    /**
     *
     * @param type $filePath The file to upload.
     * @param type $title The title of the photo.
     * @param type $description A description of the photo. May contain some limited HTML.
     * @param type $tags A space-seperated list of tags to apply to the photo.
     * @param type $contentType Set to 1 for Photo, 2 for Screenshot, or 3 for Other.
     * @return int Photo Id
     */
    public function uploadSync($filePath, $title = null, $description = null, $tags = null, $contentType = 1)
    {
        try {
            return $this->_upload($filePath, $title, $description, $tags, $contentType, 0);
        } catch (\Exception $e) {
            if ($e->getCode() == self::ERR_RESPONSE_NOT_XML) {
                // Retry
                return $this->_upload($filePath, $title, $description, $tags, $contentType, 0);
            } else {
                throw $e;
            }
        }
    }

    /**
     *
     * @param type $filePath The file to upload.
     * @param type $title The title of the photo.
     * @param type $description A description of the photo. May contain some limited HTML.
     * @param type $tags A space-seperated list of tags to apply to the photo.
     * @param type $contentType Set to 1 for Photo, 2 for Screenshot, or 3 for Other.
     * @return string Ticket Id
     */
    public function uploadAsync($filePath, $title = null, $description = null, $tags = null, $contentType = 1)
    {
        try {
            return $this->_upload($filePath, $title, $description, $tags, $contentType, 1);
        } catch (\Exception $e) {
            if ($e->getCode() == self::ERR_RESPONSE_NOT_XML) {
                // Retry
                return $this->_upload($filePath, $title, $description, $tags, $contentType, 1);
            } else {
                throw $e;
            }
        }
    }

    /**
     *
     * @param type $filePath The file to upload.
     * @param type $photoId The ID of the photo to replace.
     * @return int Photo Id
     */
    public function replaceSync($filePath, $photoId)
    {
        try {
            return $this->_replace($filePath, $photoId, 0);
        } catch (\Exception $e) {
            if ($e->getCode() == self::ERR_RESPONSE_NOT_XML) {
                // Retry
                return $this->_replace($filePath, $photoId, 0);
            } else {
                throw $e;
            }
        }
    }

    /**
     *
     * @param type $filePath The file to upload.
     * @param type $photoId The ID of the photo to replace.
     * @return string Ticket Id
     */
    public function replaceAsync($filePath, $photoId)
    {
        try {
            return $this->_replace($filePath, $photoId, 1);
        } catch (\Exception $e) {
            if ($e->getCode() == self::ERR_RESPONSE_NOT_XML) {
                // Retry
                return $this->_replace($filePath, $photoId, 1);
            } else {
                throw $e;
            }
        }
    }

}
