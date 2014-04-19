<?php

namespace Itscaro\Service\Flickr\Model\Photo;

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

    public function __construct(array $rawData = array())
    {
        parent::__construct($rawData);

        $this->photo = null;
    }

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->photo[] = new Photo($_item);
        }
    }

}
