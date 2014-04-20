<?php

namespace Itscaro\Service\Flickr\Model\Photo;

use Itscaro\Service\Flickr\Model\ModelAbstract;

/**
 * Description of Photo
 *
 * @author Minh-Quan
 */
class PhotoInfo extends ModelAbstract {

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
    public $dateuploaded;

    /**
     *
     * @var int 
     */
    public $isfavorite;

    /**
     *
     * @var string 
     */
    public $license;

    /**
     *
     * @var string 
     */
    public $safety_level;

    /**
     *
     * @var int 
     */
    public $rotation;

    /**
     *
     * @var string 
     */
    public $originalsecret;

    /**
     *
     * @var string 
     */
    public $originalformat;

    /**
     *
     * @var array
     */
    public $owner;

    /**
     *
     * @var array
     */
    public $title;

    /**
     *
     * @var array
     */
    public $description;

    /**
     *
     * @var array
     */
    public $visibility;

    /**
     *
     * @var array
     */
    public $dates;

    /**
     *
     * @var array
     */
    public $permissions;

    /**
     *
     * @var string
     */
    public $views;

    /**
     *
     * @var array
     */
    public $editability;

    /**
     *
     * @var array
     */
    public $publiceditability;

    /**
     *
     * @var array
     */
    public $usage;

    /**
     *
     * @var array
     */
    public $comments;

    /**
     *
     * @var array
     */
    public $notes;

    /**
     *
     * @var array
     */
    public $people;

    /**
     *
     * @var array
     */
    public $tags;

    /**
     *
     * @var array
     */
    public $urls;

    /**
     *
     * @var string
     */
    public $media;

}
