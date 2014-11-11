<?php

namespace Itscaro\Service\Flickr\Model;

/**
 * Description of Tag
 *
 * @author Minh-Quan
 */
class Tag extends ModelAbstract {

    /**
     * @todo vérifier si nécessaire?
     * @var type 
     */
    public $tag;

    /**
     *
     * @var string
     */
    public $id;

    /**
     *
     * @var string
     */
    public $author;

    /**
     *
     * @var string
     */
    public $authorname;

    /**
     *
     * @var string
     */
    public $raw;

    /**
     *
     * @var string
     */
    public $_content;

    /**
     *
     * @var boolean
     */
    public $machine_tag;

}
