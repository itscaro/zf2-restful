<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr\Model\ModelAbstract;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class SizeCollection extends ModelAbstract {

    /**
     *
     * @var int
     */
    public $canblog;

    /**
     *
     * @var int
     */
    public $canprint;

    /**
     *
     * @var int
     */
    public $candownload;

    /**
     *
     * @var Size[] 
     */
    public $size;

    public function __construct(array $rawData = array())
    {
        parent::__construct($rawData);

        $this->size = null;
    }

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->size[] = new Size($_item);
        }
    }

}
