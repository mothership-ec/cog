<?php

namespace Message\Cog;

/**
 * Services
 *
 * Our dependency injection container based on Pimple. It kind of acts
 * like a registry though, however we try and use it properly.
 */
class Services implements \ArrayAccess
{
    static protected $_instance;
    private $_values;

    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as arguments to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    function __construct (array $values = array())
    {
        $this->_values = $values;
    }

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
        return $this->_values;
    }

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to defined an object
     */
    public function offsetSet($id, $value)
    {
        $this->_values[$id] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     *
     * @return mixed  The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->_values)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->_values[$id] instanceof \Closure ? $this->_values[$id]($this) : $this->_values[$id];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param  string $id The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($id)
    {
        return isset($this->_values[$id]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        unset($this->_values[$id]);
    }

    /**
     * Returns a closure that stores the result of the given closure for
     * uniqueness in the scope of this instance.
     *
     * @param Closure $callable A closure to wrap for uniqueness
     *
     * @return Closure The wrapped closure
     */
    public function share(\Closure $callable)
    {
        return function ($c) use ($callable) {
            static $object;

            if (is_null($object)) {
                $object = $callable($c);
            }

            return $object;
        };
    }

    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param Closure $callable A closure to protect from being evaluated
     *
     * @return Closure The protected closure
     */
    public function protect(\Closure $callable)
    {
        return function ($c) use ($callable) {
            return $callable;
        };
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     *
     * @return mixed  The value of the parameter or the closure defining an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function raw($id)
    {
        if (!array_key_exists($id, $this->_values)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->_values[$id];
    }
}

