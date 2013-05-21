<?php

namespace Message\Cog\Filesystem;

use Message\Cog\ReferenceParser;


class StreamWrapperInitialiser
{
	public static $nextHandler;
	protected $_handler;

	public function __construct()
	{
		$this->_handler = self::$nextHandler;
		self::$nextHandler = null;
	}

	static function setNextHandler($instance)
	{
		self::$nextHandler = $instance;
	}

	public function __call($funcName, $args)
	{
		return call_user_func_array(array($this->_handler, $funcName), $args);
	}
}