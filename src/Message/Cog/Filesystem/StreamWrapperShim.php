<?php

namespace Message\Cog\Filesystem;

abstract class StreamWrapperShim
{
	abstract protected function _getStreamWrapperPrefix();
	protected $_handler;

	public function __construct()
	{
		// get the handler
		$this->_handler = StreamWrapperManager::getHandler();
	}

	public function __call($method, $args)
	{
		if(!method_exists($this->_handler, $method)) {
			throw new \Exception(sprintf('Unknown method `%s`', $method));
		}

		return call_user_func_array(array($this->_handler, $method), $args);
	}
}