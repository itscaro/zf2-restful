<?php

namespace Itscaro\Service\Flickr\Model\Photo;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class Size extends ModelAbstract {

    /**
     * Type
     * @var string Square, Thumbnail, Small, Medium, Large, Original, etc.
     */
    public $label;

    /**
     * Width
     * @var string 
     */
    public $width;

    /**
     * Height
     * @var string
     */
    public $height;

    /**
     * Link to the image
     * @var string 
     */
    public $source;

    /**
     * Link to the page
     * @var string
     */
    public $url;

    /**
     * Media type
     * @var string photo, video
     */
    public $media;

}
