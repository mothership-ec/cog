<?php

namespace Message\Cog\Filesystem;

class StreamWrapperManager
{
	const SHIM_CLASS_NAME = '\\Message\\Cog\\Filesystem\\StreamWrapperShim';

	static $handlers = array();

	public function register($prefix, \Closure $func)
	{
		if(isset($this->_handlers[$prefix])) {
			throw new \Exception(sprintf('Stream wrapper `%s://` already registered', $prefix));
		}

		$className = $this->_createTempClass($prefix);

		if(!stream_wrapper_register($prefix, $className)) {
			throw new \Exception(sprintf('Could not register stream wrapper `%s://`', $prefix));
		}

		static::$handlers[$prefix] = $func;
	}

	public function unregister($prefix)
	{
		if(!stream_wrapper_unregister($prefix)) {
			throw new \Exception(sprintf('Could not register stream wrapper `%s://`', $prefix));
		}

		unset(static::$handlers[$prefix]);
	}

	public function clear()
	{
		$keys = array_keys(static::$handlers);

		foreach($keys as $prefix) {
			$this->unregister($prefix);
		}
	}

	public function getHandlers()
	{
		return static::$handlers;
	}

	static public function getHandler($prefix)
	{
		if(!isset(static::$handlers[$prefix])) {
			throw new \Exception(sprintf('No handler registered for `%s://`', $prefix));
		}

		return call_user_func(static::$handlers[$prefix]);
	}

	protected function _createTempClass($prefix)
	{
		$className.= 'Tmp_'.substr(md5($prefix), 0, 12).mt_rand(100000, 999999);
		$php = '
			namespace '.__NAMESPACE__.';
			class '.$className.' extends '.self::SHIM_CLASS_NAME.' {
				public function getStreamWrapperPrefix() {
					return \''.$prefix.'\';
				}
			}
		';

		eval($php);

		return '\\'.__NAMESPACE__.'\\'.$className;
	}
}