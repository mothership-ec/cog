<?php

namespace Message\Cog\HTTP;

/**
 * Our HTTP Request class, which extends Symfony's.
 *
 * @see \Symfony\Component\HttpFoundation\Request
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
	private $mimeTypes = array();

	/**
	 * Constructor.
	 *
	 * HTTP method overriding using the special `_method` parameter is enabled
	 * by default in our Request object.
	 *
	 * @see \Symfony\Component\HttpFoundation\Request::__construct
	 * @see \Symfony\Component\HttpFoundation\Request::enableHttpMethodParameterOverride
	 */
	public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
	{
		parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

		self::enableHttpMethodParameterOverride();
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

	/**
	 * Get the allowed mime types for this request
	 * @return array
	 */
	public function getAllowedMimeTypes()
	{
		// Mime Types have already been set.
		if($this->mimeTypes)
		{
			return $this->mimeTypes;
		}

		$contentTypes = $this->getAllowedMimeTypes();

		foreach($contentTypes $as $mimeType)
		{
			$this->mimeTypes[] = $this->getFormat($mimeType);
		}

		return $this->mimeTypes;
	}
}