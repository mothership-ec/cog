<?php

namespace Message\Cog\Application;


/**
 * 
 */
class ErrorHandler
{
	public function register()
	{
		set_error_handler(array($this, 'handle'));
	}

	public function restore()
	{
		restore_error_handler();
	}

	public function handle($errno, $errstr, $errfile, $errline)
	{
		// Determine if this error is one of the enabled ones in php config (php.ini, .htaccess, etc)
		$reportingEnabled = (bool)($errno & ini_get('error_reporting'));

		// If there's no reporting we don't do anything
		if(!$reportingEnabled) {
			return true;
		}

		// For fatal errors throw an ErrorException
		if(in_array($errno, array(E_USER_ERROR, E_RECOVERABLE_ERROR))) {
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		}

		// For  NON-FATAL ERROR/WARNING/NOTICE Log the error if it's enabled, otherwise just ignore it
		error_log($errstr, 0);

		// Make sure this ends up in $php_errormsg, if appropriate
		return false; 
	}
}