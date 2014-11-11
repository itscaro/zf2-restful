<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr\Flickr;
use Itscaro\Service\Flickr\Model\ModelAbstract;
use Itscaro\Service\Flickr\Model\Tag;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class Photo extends ModelAbstract {

    /**
     *
     * @var string 
     */
    public $id;

    /**
     *
     * @var string 
     */
    public $owner;

    /**
     *
     * @var string 
     */
    public $secret;

    /**
     *
     * @var string 
     */
    public $originalsecret;

    /**
     *
     * @var string 
     */
    public $server;

    /**
     *
     * @var int 
     */
    public $farm;

    /**
     *
     * @var string 
     */
    public $title;

    /**
     *
     * @var int 
     */
    public $ispublic;

    /**
     *
     * @var int 
     */
    public $isfriend;

    /**
     *
     * @var int 
     */
    public $isfamily;

    /**
     * 
     * @return Tag[]
     */
    public function getTags()
    {
        $flickr = Flickr::getInstance();
    
        /* @var $flickr Flickr */
        $result = $flickr->getClient()->get('flickr.tags.getListPhoto', array(
            'photo_id' => $this->id
        ));
        
        $return = array();
        if (isset($result['stat']) && $result['stat'] == 'ok') {
            foreach($result['photo']['tags']['tag'] as $_tag) {
                $return[$_tag['raw']] = new Tag($this->_flickrApi, $_tag);
            }
        }
        return $return;
    }

    /**
     * 
     * https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}.jpg
     *  or
     * https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}_[mstzb].jpg
     *  or
     * https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{o-secret}_o.(jpg|gif|png)
     * 
     * @param string $size
     */
    public function getUrl($size = null)
    {
//        s	small square 75x75
//        q	large square 150x150
//        t	thumbnail, 100 on longest side
//        m	small, 240 on longest side
//        n	small, 320 on longest side
//        -	medium, 500 on longest side
//        z	medium 640, 640 on longest side
//        c	medium 800, 800 on longest side†
//        b	large, 1024 on longest side*
//        h	large 1600, 1600 on longest side†
//        k	large 2048, 2048 on longest side†
//        o	original image, either a jpg, gif or png, depending on source format

        if (in_array($size, array('m', 's', 't', 'z', 'b'))) {
            return "https://farm{$this->farm}.staticflickr.com/{$this->server}/{$this->id}_{$this->secret}_{$size}.jpg";
        } else {
            return "https://farm{$this->farm}.staticflickr.com/{$this->server}/{$this->id}_{$this->secret}.jpg";
        }
    }
}
