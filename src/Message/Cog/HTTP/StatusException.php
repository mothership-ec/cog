<?php

namespace Message\Cog\HTTP;

/**
 * Exception object for throwing exceptions that should relate to a specific
 * HTTP status code in the response.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusException extends \Exception
{
	const FORBIDDEN           = 303;

	const NOT_FOUND           = 404;
	const NOT_ALLOWED         = 405;
	const NOT_ACCEPTABLE      = 406;

	const SERVER_ERROR        = 500;
	const SERVICE_UNAVAILABLE = 503;
}