<?php

namespace Message\Cog;

use \Pimple;

/**
 * Services
 *
 * Our dependency injection container which extends Pimple. It kind of acts
 * like a registry with it's static instance() method, however we try and 
 * use it responsibly.
 */
class Services extends Pimple
{
    static protected $_instance;

    /**
     * Singleton accessor
     *
     * @return Services
     */
    static public function instance()
    {
        if(!isset(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Gets a service from the dependancy injection container.
     *
     * @param  string $id Unique identifier of the service you want
     * @return mixed
     */
    static public function get($id)
    {
        return self::instance()->offsetGet($id);
    }

    /**
     * Sets a parameter or an object in the dependancy injection container.
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to defined an object
     */
    static public function set($id, $value)
    {
        self::instance()->offsetSet($id, $value);
    }

    /**
     * Get a list of all the services.
     *
     * @return array a hash where the service name is the key.
     */
    public function getAll()
    {
        return $this->values;
    }
}