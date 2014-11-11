<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr;
use Itscaro\Service\Flickr\Model\ModelAbstract;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class PhotoCollection extends ModelAbstract {

    /**
     *
     * @var int 
     */
    public $page;

    /**
     *
     * @var int 
     */
    public $pages;

    /**
     *
     * @var int 
     */
    public $perpage;

    /**
     *
     * @var string 
     */
    public $total;

    /**
     *
     * @var Photo[] 
     */
    public $photo = array();

    public function addItems(array $items)
    {
        $mapper = new \JsonMapper();
        $this->photo = $mapper->mapArray($items, $this->photo, new Photo());
    }

}
