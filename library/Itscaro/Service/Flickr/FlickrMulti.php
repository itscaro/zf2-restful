<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;

/**
 * Wrapper
 *
 * @author Minh-Quan
 */
class FlickrMulti {

    /**
     *
     * @var ClientMulti
     */
    protected $_client;

    /**
     *
     * @var string
     */
    protected $_endpoint = 'https://api.flickr.com/services/rest/';

    /**
     *
     * @var array
     */
    protected $_queue = array();

    public function __construct(ZendOAuth\Token\Access $accessToken, array $optionsOAuth = array(), array $optionsHttpClient = array())
    {
        $this->_client = new ClientMulti($this->_endpoint, $optionsOAuth, $optionsHttpClient);
        $this->_client->setAccessToken($accessToken);
    }

    /**
     *
     * @return ZendOAuth\Token\Access
     */
    public function getAccessToken()
    {
        return $this->_client->getAccessToken();
    }

    /**
     * 
     * @return ClientMulti
     */
    public function getClient()
    {
        return $this->_client;
    }
    
    /**
     *
     */
    public function dispatch()
    {
        $responses = $this->_client->dispatchMulti();
        $this->_client->getRestClient()->reset();

        foreach ($responses as $_requestId => &$_result) {
            $_result = json_decode($_result, true);

            switch ($this->_queue[$_requestId]) {
                case 'flickr.photosets.getList':
                    foreach ($_result['photosets']['photoset'] as $_set) {
                        $sets[$_set['id']] = $_set['title']['_content'];
                    }
                    break;

                case 'flickr.tags.getListUser':
                    foreach ($_result['who']['tags']['tag'] as $_tag) {
                        $tags[$_tag['_content']] = $_tag['_content'];
                    }
                    break;

                default:
                    break;
            }
        }

        $this->_queue = array();

        return $responses;
    }

    /**
     * Retrieves a list of EXIF/TIFF/GPS tags for a given photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param string $secret Secret of the photo
     * @param array $params
     * @return FlickrMulti
     */
    public function photoGetExif($photoId, $secret = null, array $params = array())
    {
        $method = 'flickr.photos.getExif';

        $params['photo_id'] = $photoId;
        if ($secret !== null) {
            $params['secret'] = $secret;
        }

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Get information about a photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param string $secret Secret of the photo
     * @param array $params
     * @return FlickrMulti
     */
    public function photoGetInfo($photoId, $secret = null, array $params = array())
    {
        $method = 'flickr.photos.getInfo';

        $params['photo_id'] = $photoId;
        if ($secret !== null) {
            $params['secret'] = $secret;
        }

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Returns the available sizes for a photo.
     * The calling user must have permission to view the photo.
     * @param string $photoId Photo Id
     * @param array $params
     * @return FlickrMulti
     */
    public function photoGetSizes($photoId, array $params = array())
    {
        $method = 'flickr.photos.getSizes';

        $params['photo_id'] = $photoId;

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Add tags to a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param array $tags Tags
     * @param array $params
     * @return FlickrMulti
     */
    public function photoAddTags($photoId, array $tags, array $params = array())
    {
        $method = 'flickr.photos.addTags';

        $params['photo_id'] = $photoId;
        $params['tags'] = $this->_tagsArrayToString($tags);

        $requestId = $this->_client->addToQueue('POST', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Set the tags for a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param array $tags Tags
     * @param array $params
     * @return FlickrMulti
     */
    public function photoSetTags($photoId, array $tags, array $params = array())
    {
        $method = 'flickr.photos.setTags';

        $params['photo_id'] = $photoId;
        $params['tags'] = $this->_tagsArrayToString($tags);

        $requestId = $this->_client->addToQueue('POST', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Remove a tag from a photo.
     * This method used HTTP POST
     * @param string $tagId Tag Id
     * @param array $params
     * @return FlickrMulti
     */
    public function photoRemoveTag($tagId, array $params = array())
    {
        $method = 'flickr.photos.removeTag';

        $params['tag_id'] = $tagId;

        $requestId = $this->_client->addToQueue('POST', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Set the tags for a photo.
     * This method used HTTP POST
     * @param string $photoId Photo Id
     * @param string $title Photo title
     * @param string $description Photo description
     * @param array $params
     * @return FlickrMulti
     */
    public function photoSetMeta($photoId, $title, $description, array $params = array())
    {
        $method = 'flickr.photos.setMeta';

        $params['photo_id'] = $photoId;
        $params['title'] = $title;
        $params['description'] = $description;

        $requestId = $this->_client->addToQueue('POST', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Return a list of photos matching some criteria.
     * Only photos visible to the calling user will be returned.
     * To return private or semi-private photos, the caller must be authenticated with 'read' permissions, and have permission to view the photos.
     * Unauthenticated calls will only return public photos.
     * @see https://www.flickr.com/services/api/flickr.photos.search.html
     * @param array $params
     * @return FlickrMulti
     */
    public function photoSearch(array $params = array())
    {
        $method = 'flickr.photos.search';

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Returns the photosets belonging to the specified user
     * @param string $userId
     * @return FlickrMulti
     */
    public function photosetGetList($userId = null)
    {
        $method = 'flickr.photosets.getList';

        $params = array();
        if ($userId !== null) {
            $params = array(
                'user_id' => $userId
            );
        }

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
    }

    /**
     * Get the tag list for a given user (or the currently logged in user)
     * @param string $userId
     * @return FlickrMulti
     */
    public function tagGetListUser($userId = null)
    {
        $method = 'flickr.tags.getListUser';

        $params = array();
        if ($userId !== null) {
            $params = array(
                'user_id' => $userId
            );
        }

        $requestId = $this->_client->addToQueue('GET', $method, $params);

        $this->_queue[$requestId] = $method;

        return $this;
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
