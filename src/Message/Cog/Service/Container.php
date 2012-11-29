<?php

namespace Message\Cog\Service;

/**
 * Service container.
 *
 * Our dependency injection container which extends Pimple. It kind of acts
 * like a registry with its static `instance()` method, however we try and
 * use it responsibly.
 */
class Container extends \Pimple implements ContainerInterface
{
	static protected $_instance;

	/**
     * Singleton accessor
     *
     * @return Container The instance of self
	 */
	static public function instance()
	{
		if (!isset(self::$_instance)) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
     * Gets a service.
     *
     * @param  string $id Unique identifier for the service
     * @return mixed
	 */
	static public function get($id)
	{
		return self::instance()->offsetGet($id);
	}

	/**
     * Get an array of all the defined services.
     *
     * @return array The full list of services, where the ID is the key
	 */
	public function getAll()
	{
        $return = array();

        foreach ($this->keys() as $key) {
            $return[$key] = $this->offsetGet($key);
        }

		return $return;
	}
}