<?php

namespace Itscaro\Service\Flickr;

use Itscaro\Rest;

abstract class ClientAbstract {

    /**
     *
     * @var string
     */
    protected $_endpoint;

    /**
     *
     * @var Rest\Client 
     */
    protected $_restClient;

    function __construct($endpoint)
    {
        $this->setEndpoint($endpoint);
    }

    /**
     * 
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        return Rest\Client::getHttpClient();
    }

    /**
     * 
     * @param \Zend\Http\Client $httpClient
     * @return \Itscaro\Service\Flickr\Client
     */
    public function setHttpClient(\Zend\Http\Client $httpClient)
    {
        Rest\Client::setHttpClient($httpClient);

        return $this;
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
     * @return ClientAbstract
     */
    public function getRestClient()
    {
        if ($this->_restClient == null) {
            $restClient = new Rest\Client();
            $this->setRestClient($restClient);
        }

        return $this->_restClient;
    }

    /**
     * 
     * @param Rest\Client $restClient
     * @return ClientAbstract
     */
    public function setRestClient(Rest\Client $restClient)
    {
        $this->_restClient = $restClient;
        return $this;
    }

    /**
     * 
     * @param array $params
     * @return object | array
     * @throws Exception
     */
    abstract protected function dispatch($httpMethod, $method, array $params = array());

    /**
     * Retrieves a list of EXIF/TIFF/GPS tags for a given photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param string $secret Secret of the photo
     * @param array $extraParams
     * @return object | array
     */
    public function photoGetExif($photoId, $secret = null, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;
        if ($secret !== null) {
            $extraParams['secret'] = $secret;
        }

        $result = $this->dispatch('GET', 'flickr.photos.getExif', $extraParams);

        $exifs = new Flickr\Model\Photo\ExifCollection($result['photo']);
        if (isset($result['photo']['exif']) && is_array($result['photo']['exif'])) {
            $exifs->addItems($result['photo']['exif']);
        }

        return $exifs;
    }

    /**
     * Get information about a photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param string $secret Secret of the photo
     * @param array $extraParams
     * @return object | array
     */
    public function photoGetInfo($photoId, $secret = null, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;
        if ($secret !== null) {
            $extraParams['secret'] = $secret;
        }

        $result = $this->dispatch('GET', 'flickr.photos.getInfo', $extraParams);

        return new Flickr\Model\Photo\PhotoInfo($result['photo']);
    }

    /**
     * Returns the available sizes for a photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param array $extraParams
     * @return object | array
     */
    public function photoGetSizes($photoId, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;

        $result = $this->dispatch('GET', 'flickr.photos.getSizes', $extraParams);

        $sizes = new Flickr\Model\Photo\SizeCollection($result['sizes']);
        if (isset($result['sizes']['size']) && is_array($result['sizes']['size'])) {
            $sizes->addItems($result['sizes']['size']);
        }

        return $sizes;
    }

    /**
     * Add tags to a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param array $tags Tags
     * @param array $extraParams
     * @return object | array
     */
    public function photoAddTags($photoId, array $tags, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;
        $extraParams['tags'] = $this->_tagsArrayToString($tags);

        return $this->dispatch('POST', 'flickr.photos.addTags', $extraParams);
    }

    /**
     * Set the tags for a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param array $tags Tags
     * @param array $extraParams
     * @return object | array
     */
    public function photoSetTags($photoId, array $tags, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;
        $extraParams['tags'] = $this->_tagsArrayToString($tags);

        return $this->dispatch('POST', 'flickr.photos.setTags', $extraParams);
    }

    /**
     * Remove a tag from a photo.
     * This method used HTTP POST
     * @param string $tagId Tag Id
     * @param array $extraParams
     * @return object | array
     */
    public function photoRemoveTag($tagId, array $extraParams = array())
    {
        $extraParams['tag_id'] = $tagId;

        return $this->dispatch('POST', 'flickr.photos.removeTag', $extraParams);
    }

    /**
     * Set the tags for a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param string $title Photo title
     * @param string $description Photo description
     * @param array $extraParams
     * @return object | array
     */
    public function photoSetMeta($photoId, $title, $description, array $extraParams = array())
    {
        $extraParams['photo_id'] = $photoId;
        $extraParams['title'] = $title;
        $extraParams['description'] = $description;

        return $this->dispatch('POST', 'flickr.photos.setMeta', $extraParams);
    }

    /**
     * Return a list of photos matching some criteria.
     * Only photos visible to the calling user will be returned.
     * To return private or semi-private photos, the caller must be authenticated with 'read' permissions, and have permission to view the photos.
     * Unauthenticated calls will only return public photos.
     * @see https://www.flickr.com/services/api/flickr.photos.search.html
     * @param array $extraParams
     * @return object | array
     */
    public function photoSearch(array $extraParams = array())
    {
        $result = $this->dispatch('GET', 'flickr.photos.search', $extraParams);

        $photos = new Flickr\Model\Photo\PhotoCollection($result['photos']);
        if (isset($result['photos']['photo']) && is_array($result['photos']['photo'])) {
            $photos->addItems($result['photos']['photo']);
        }

        return $photos;
    }

    /**
     * Convert internal tags array to Flickr format (single space-delimited string)
     * @param array $tags
     * @return string
     */
    protected function _tagsArrayToString(array $tags = array())
    {
        //All tags for the photo (as a single space-delimited string)
        array_walk($tags, function(&$item) {
            $item = str_replace(' ', '-', $item);
        });

        return implode(' ', $tags);
    }

}
