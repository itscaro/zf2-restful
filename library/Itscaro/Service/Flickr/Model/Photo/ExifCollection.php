<?php

namespace Itscaro\Service\Flickr\Model\Photo;

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
     * @var string 
     */
    public $farm;

    /**
     *
     * @var string 
     */
    public $camera;

    /**
     *
     * @var array 
     */
    public $exif = array();

    public function addItems(array $items)
    {
        foreach ($items as $_item) {
            $this->exif[] = new Exif($_item);
        }
    }

}
