<?php

namespace Itscaro\Service\Flickr;

/**
 * Description of Exception
 *
 * @author Minh-Quan
 */
class Exception extends \Exception {

    /**
     *
     * @var string
     */
    protected $_stat;

    /**
     * 
     * @return string
     */
    public function getStat()
    {
        return $this->_stat;
    }

    /**
     * 
     * @param string $stat
     * @return Exception
     */
    public function setStat($stat)
    {
        $this->_stat = $stat;
        return $this;
    }

}
