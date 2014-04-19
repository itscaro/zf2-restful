<?php

namespace Itscaro\Service\Flickr\Model\Photo;

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
     * @var array 
     */
    public $size;

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->size[] = new Size($_item);
        }
    }

}
