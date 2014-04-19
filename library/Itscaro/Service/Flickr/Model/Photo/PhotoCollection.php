<?php

namespace Itscaro\Service\Flickr\Model\Photo;

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
     * @var array 
     */
    public $photo = array();

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->photo[] = new Photo($_item);
        }
    }

}
