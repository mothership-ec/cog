<?php

namespace Message\Cog\Filesystem;

/**
 * StreamWrapperShim
 *
 * An instance of this class is always passed to stream_wrapper_register via StreamWrapperManager.
 * It acts as a proxy between php internals and the class that implements the StreamWrapperInterface and allows
 * dependencies to be injected rather than hard coded.
 */
abstract class StreamWrapperShim
{
	abstract public function getStreamWrapperPrefix();

	protected $_handler;

	/**
	 * Loads the handler and sets up this proxy class.
	 * getHandler() has to be called statically (boo) as it's the only way to reference
	 * the world outside of this instance.
	 */
	public function __construct()
	{
		// get the handler
		$prefix = $this->getStreamWrapperPrefix();
		$this->_handler = StreamWrapperManager::getHandler($prefix);
		$this->_handler->prefix = $prefix;
	}

	/**
	 * Proxies method calls through to the handler.
	 */
	public function __call($method, $args)
	{
		// Sometimes php creates an instead of our StreamWrapper class without calling the constructor!
		// This happens with the unlink() function, so we have to check that our handler is setup before
		// we test to see if the $method exists.
		if(!($this->_handler instanceof StreamWrapperInterface)) {
			$this->__construct();
		}

		if(!method_exists($this->_handler, $method)) {
			throw new \Exception(sprintf('Unknown method `%s`', $method));
		}

		return call_user_func_array(array($this->_handler, $method), $args);
	}

	/**
	 * Proxies property access through to the handler.
	 */
	public function __get($name)
	{
		if(!isset($this->_handler->{$name})) {
			throw new \Exception('Unknown property ' . $name);
		}

		return $this->_handler->{$name};
	}

	/**
	 * Proxies property setting through to the handler.
	 */
	public function __set($name, $value)
	{
		$this->_handler->{$name} = $value;
	}

	/**
	 * Proxies isset access through to the handler.
	 */
	public function __isset($property)
	{
		return isset($this->_handler->{$name});
	}

}