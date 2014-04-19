<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr\Model\ModelAbstract;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class ExifCollection extends ModelAbstract {

    /**
     *
     * @var string 
     */
    public $id;

    /**
     *
     * @var string 
     */
    public $secret;

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
    public $camera;

    /**
     *
     * @var Exif[] 
     */
    public $exif = array();

    public function __construct(array $rawData = array())
    {
        parent::__construct($rawData);

        $this->exif = null;
    }

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->exif[] = new Exif($_item);
        }
    }

}
