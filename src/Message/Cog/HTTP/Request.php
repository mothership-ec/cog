<?php

namespace Message\Cog\HTTP;

/**
 * Our HTTP Request class. Extends Symfony's.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
	protected $_internal = false;

	/**
	 * Sets this request as an internal request (sub-request)
	 */
	public function setInternal()
	{
		$this->_internal = true;
	}

	/**
	 * Returns true if this request is an internal request (sub-request)
	 *
	 * @return boolean Result of the check
	 */
	public function isInternal()
	{
		return $this->_internal;
	}

	/**
	 * Returns true if this request is an external request (master request)
	 *
	 * @return boolean Result of the check
	 */
	public function isExternal()
	{
		return !$this->_internal;
	}

	/**
	 * Gets the allowed content types for this request.
	 *
	 * If the route only allows certain formats, then these will be determined
	 * in the `_allowedContentTypes` attribute. Otherwise we can assume that
	 * all the requested content types are allowed.
	 *
	 * @return array The allowed content types
	 */
	public function getAllowedContentTypes()
	{
		if ($this->attributes->has('_allowedContentTypes')) {
			return $this->attributes->get('_allowedContentTypes');
		}

		return $this->getAcceptableContentTypes();
	}
}