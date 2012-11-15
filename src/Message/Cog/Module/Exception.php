<?php

namespace Message\Cog\Module;

/**
 * Module exceptions.
 *
 * Exception codes in the 2000xxxx range are reserved for this Exception
 * object.
 */
class Exception extends \Exception
{
	// EXCEPTION CODES [2000xxxx]
	const NO_MODULES_FOUND      = 20000000;
	const MODULE_NOT_FOUND      = 20000001;
}