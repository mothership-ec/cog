<?php

namespace Message\Cog\HTTP;

/**
 * Interface for classes that are aware of the current HTTP request.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface RequestAwareInterface
{
	/**
	 * Sets the HTTP request on this class.
	 *
	 * @param Request $request The request instance
	 */
	public function setRequest(Request $request);
}