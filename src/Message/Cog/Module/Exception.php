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
	const DOCUMENT_NOT_FOUND    = 20000002;
	const DOCUMENT_NOT_READABLE = 20000003;
	const DOCUMENT_INVALID      = 20000004;
}