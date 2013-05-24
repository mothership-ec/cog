<?php

namespace Message\Cog\Filesystem;

abstract class StreamWrapperShim
{
	abstract public function getStreamWrapperPrefix();

	protected $_handler;

	public function __construct()
	{
		// get the handler
		$prefix = $this->getStreamWrapperPrefix();
		$this->_handler = StreamWrapperManager::getHandler($prefix);
		$this->_handler->prefix = $prefix;
	}

	public function __call($method, $args)
	{
		if(!method_exists($this->_handler, $method)) {
			throw new \Exception(sprintf('Unknown method `%s`', $method));
		}

		return call_user_func_array(array($this->_handler, $method), $args);
	}

	public function __get($name)
	{
		if(!isset($this->_handler->{$name})) {
			throw new \Exception('Unknown property ' . $name);
		}

		return $this->_handler->{$name};
	}

	public function __set($name, $value)
	{
		$this->_handler->{$name} = $value;
	}

	public function __isset($property)
	{
		return isset($this->_handler->{$name});
	}
}