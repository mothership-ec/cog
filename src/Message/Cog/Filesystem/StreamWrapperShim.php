<?php

namespace Message\Cog\Filesystem;

/**
 * StreamWrapperShim
 *
 * An instance of this class is always passed to stream_wrapper_register via StreamWrapperManager.
 * It acts as a proxy between php internals and the class that implements the StreamWrapperInterface and allows
 * dependencies to be injected rather than hard coded.
 *
 * @author  James Moss <james@message.co.uk>
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
	}

	/**
	 * Proxies method calls through to the handler.
	 */
	public function __call($method, $args)
	{
		$this->_checkHandlerIsInitialised();
		
		if(!method_exists($this->_handler, $method)) {
			throw new \BadMethodCallException(sprintf('Unknown method `%s`', $method));
		}

		return call_user_func_array(array($this->_handler, $method), $args);
	}

	/**
	 * Proxies property access through to the handler.
	 */
	public function __get($name)
	{
		$this->_checkHandlerIsInitialised();

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
		$this->_checkHandlerIsInitialised();

		$this->_handler->{$name} = $value;
	}

	/**
	 * Proxies isset access through to the handler.
	 */
	public function __isset($property)
	{
		$this->_checkHandlerIsInitialised();
		
		return isset($this->_handler->{$name});
	}

	/**
	 * Sometimes php creates an instance of our StreamWrapper class without calling the constructor!
	 * This happens with the unlink() function and a few others, so we have to check that our handler
	 * is setup before we act on the _handler object.
	 */
	protected function _checkHandlerIsInitialised()
	{
		if(!($this->_handler instanceof StreamWrapperInterface)) {
			$this->__construct();
		}
	}

}