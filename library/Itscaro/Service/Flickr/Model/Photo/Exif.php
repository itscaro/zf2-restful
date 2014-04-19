<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr\Model\ModelAbstract;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class Exif extends ModelAbstract {

    /**
     *
     * @var string 
     */
    public $tagspace;

    /**
     *
     * @var int 
     */
    public $tagspaceid;

    /**
     *
     * @var string 
     */
    public $tag;

    /**
     *
     * @var string 
     */
    public $label;

    /**
     *
     * @var array 
     */
    public $raw;

    /**
     *
     * @var array 
     */
    public $clean;

}
