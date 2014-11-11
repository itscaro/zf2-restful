<?php

namespace Itscaro\Service\Flickr\Model;

use Itscaro\Service\Flickr;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
abstract class ModelAbstract {

    public function __construct(array $rawData = array())
    {
//        foreach ($rawData as $_key => $_data) {
//            $this->{$_key} = $_data;
//        }
        $mapper = new \JsonMapper();
        $mapper->map($rawData, $this);
    }

}
