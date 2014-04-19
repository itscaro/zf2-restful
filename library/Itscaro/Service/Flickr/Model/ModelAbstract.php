<?php

namespace Itscaro\Service\Flickr\Model;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class ModelAbstract {

    /**
     *
     * @var array
     */
    protected $_rawData;

    public function __construct(array $rawData = array())
    {
        foreach ($rawData as $_key => $_data) {
            if (!property_exists($this, $_key)) {
                throw new \Exception(sprintf("Class '%s' does not have property '%s'.", get_class($this), $_key));
            }
            $this->{$_key} = $_data;
        }
    }

}
