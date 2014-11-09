<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;

/**
 * Wrapper
 *
 * @author Minh-Quan
 */
class Flickr {

    /**
     *
     * @var Client
     */
    protected $_client;

    /**
     *
     * @var string
     */
    protected $_endpoint = 'https://api.flickr.com/services/rest/';

    public function __construct(ZendOAuth\Token\Access $accessToken, array $optionsOAuth = array(), array $optionsHttpClient = array())
    {
        $this->_client = new Client($this->_endpoint, $optionsOAuth, $optionsHttpClient);
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
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

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

        $result = $this->getClient()->get('flickr.photos.getExif', $extraParams);

        $exifs = new Model\Photo\ExifCollection($result['photo']);
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

        $result = $this->getClient()->get('flickr.photos.getInfo', $extraParams);

        return new Model\Photo\PhotoInfo($result['photo']);
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

        $result = $this->getClient()->get('flickr.photos.getSizes', $extraParams);

        $sizes = new Model\Photo\SizeCollection($result['sizes']);
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

        return $this->getClient()->post('flickr.photos.addTags', $extraParams);
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

        return $this->getClient()->post('flickr.photos.setTags', $extraParams);
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

        return $this->getClient()->post('flickr.photos.removeTag', $extraParams);
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

        return $this->getClient()->post('flickr.photos.setMeta', $extraParams);
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
    public function photoSearch(array $extraParams = array(), Model\Photo\PhotoCollection $photos = null)
    {
        $result = $this->getClient()->get('flickr.photos.search', $extraParams);

        if ($photos === null) {
            $photos = new Model\Photo\PhotoCollection($result['photos']);
        } else {
            $photos->page = $result['photos']['page'];
            $photos->pages = $result['photos']['pages'];
            $photos->perpage = $result['photos']['perpage'];
            $photos->total = $result['photos']['total'];
        }

        if (isset($result['photos']['photo']) && is_array($result['photos']['photo'])) {
            $photos->addItems($result['photos']['photo']);
        }

        return $photos;
    }

    public function photoSearchAll(array $extraParams = array(), Model\Photo\PhotoCollection $photos = null)
    {
        if ($photos === null) {
            $photos = new Model\Photo\PhotoCollection();
        }

        if (isset($extraParams['page'])) {
            $photos->page = $extraParams['page'];            
        }
        
        while ($photos->page == 0 || $photos->page < $photos->pages) {
            $extraParams['page'] = $photos->page + 1;
            $this->photoSearch($extraParams, $photos);
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

    protected function _sanitizeTag($string)
    {
        return preg_replace('/[^a-z0-9-_]/i', '_', $string);
    }
}
