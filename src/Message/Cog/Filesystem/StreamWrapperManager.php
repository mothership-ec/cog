<?php

namespace Message\Cog\Filesystem;

/**
 * StreamWrapperManager
 *
 * Registers and unregisters stream wrappers. Provides a way of injecting
 * dependencies into a StreamWrapper class (which is normally quite difficult).
 */
class StreamWrapperManager
{
	/**
	 * The fully qualified class name of the shim file.
	 */
	const SHIM_CLASS_NAME = '\\Message\\Cog\\Filesystem\\StreamWrapperShim';

	/**
	 * An array of handlers registered for prefixes.
	 */
	static $handlers = array();

	/**
	 * Registers a new stream wrapper
	 *
	 * @param  string   $prefix the prefix to register against (this appears before the ://)
	 * @param  \Closure $func   Code to be executed when a prefixed file is accessed, the stream wrapper instance
	 *                          must be returned from this callable.
	 */
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

	/**
	 * Unregisters a steam previously set via register()
	 *
	 * @param  string $prefix The stream to unregister
	 *
	 * @return boolean        Returns true if the stream was unregistered.
	 */
	public function unregister($prefix)
	{
		if(!stream_wrapper_unregister($prefix)) {
			throw new \Exception(sprintf('Could not register stream wrapper `%s://`', $prefix));
		}

		unset(static::$handlers[$prefix]);

		return true;
	}

	/**
	 * Clears all registered stream wrappers.
	 */
	public function clear()
	{
		$keys = array_keys(static::$handlers);

		foreach($keys as $prefix) {
			$this->unregister($prefix);
		}
	}

	/**
	 * Gets an array of all registered handlers. Due to the way php works we have to use a static 
	 *
	 * @return array The array of handlers, the key is the prefix.
	 */
	public function getHandlers()
	{
		return static::$handlers;
	}

	/**
	 * Gets the callback to execute for a prefix. This has to be called statically from within StreamWrapperShim.
	 *
	 * The class that is returned from the callback must implement StreamWrapperInterface.
	 *
	 * @param  string $prefix The prefix to get the ballback for
	 *
	 * @return StreamWrapperInterface
	 */
	static public function getHandler($prefix)
	{
		if(!isset(static::$handlers[$prefix])) {
			throw new \Exception(sprintf('No handler registered for `%s://`', $prefix));
		}

		$handler = call_user_func(static::$handlers[$prefix]);
		
		if(!in_array('Message\\Cog\\Filesystem\\StreamWrapperInterface', class_implements($handler))) {
			throw new \Exception(sprintf('StreamWrapper for `%s` must implement StreamWrapperInterface', $prefix));
		}

		return $handler;
	}

	/**
	 * Creates a temporary class that extends the shim which knows its prefix. This is the only way to get data
	 * into the instance of the stream wrapper. 
	 *
	 * @param  string $prefix The prefix to create a temporary wrapper for.
	 *
	 * @return string         The fully qualified name of the temporary class.
	 */
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

		$fullClassName = '\\'.__NAMESPACE__.'\\'.$className;

		if(!class_exists($fullClassName)) {
			throw new \Exception(sprintf(
				'Could not create temporary class for stream wrapper `%s`. eval() is possibly disabled.', 
				$prefix
			));
		}

		return $fullClassName;
	}
}