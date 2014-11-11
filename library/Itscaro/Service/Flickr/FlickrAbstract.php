<?php

namespace Itscaro\Service\Flickr;

use ZendOAuth;

/**
 * Wrapper
 *
 * @author Minh-Quan
 */
abstract class FlickrAbstract {

    const FLICKR_API = 'https://api.flickr.com/services/rest/';

    protected static $_instance;

    public static function getInstance()
    {
        if (isset(static::$_instance)) {
            return static::$_instance;
        } else {
            throw new \Exception("Instance was not created");
        }
    }

    public static function createInstance(ZendOAuth\Token\Access $accessToken, array $optionsOAuth = array(), array $optionsHttpClient = array())
    {
        if (!isset(static::$_instance)) {
            static::$_instance = new self($accessToken, $optionsOAuth, $optionsHttpClient);
        } else {
            throw new \Exception("Instance was already created");
        }
    }

}
