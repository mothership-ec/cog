<?php

namespace Message\Cog\Routing;

class Route extends \Symfony\Component\Routing\Route
{
	protected $_defaultFormat = 'html';
	protected $_defaultAccess = 'external';

	public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
	{
		parent::__construct($pattern, $defaults, $requirements, $options);
		
		// Set default format to HTML if none is explicitly set
		if(!isset($defaults['_format'])) {
			$this->setFormat($this->_defaultFormat);
		}

		// Set default access to external if none is explicitly set
		if(!isset($defaults['_access'])) {
			$this->setAccess($this->_defaultAccess);
		}
	}

	public function setScheme($scheme)
	{
		return $this->setRequirement('_scheme', $scheme);
	}

	public function setMethod($method)
	{
		return $this->setRequirement('_method', strtoupper($method));
	}

	public function setFormat($format)
	{
		return $this->setDefault('_format', $format);
	}

	/**
	 * Set whether the route is internal or external. Internal routes can only
	 * be accessed by sub requests, not the master request. External routes can
	 * be access by both the sub and master requests.
	 *
	 * @param $string $access Set the access level. Valid options: 'internal', 'external'
	 */
	public function setAccess($access)
	{
		if(!in_array($access, array('internal', 'external'))) {
			throw new \Exception(sprintf('Invalid route access level `%s`', $access));
		}

		return $this->setDefault('_access', $access);
	}

	/**
	 * Make a route parameter (or route parameters) optional.
	 *
	 * @param mixed $params Parameter name (or array of parameter names)
	 * @return Route 		Returns $this for chaining
	 */
	public function setOptional($params)
	{
		$params = (array)$params;

		foreach ($params as $param) {
			$this->setRequirement($param, '.*');
		}

		return $this;
	}
}