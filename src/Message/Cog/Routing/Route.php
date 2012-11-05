<?php

namespace Message\Cog\Routing;

use Symfony\Component\Routing\Route as BaseRoute;

class Route extends BaseRoute
{
	public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
	{
		parent::__construct($pattern, $defaults, $requirements, $options);
		// Set default format to HTML if none is explicitly set
		$this->setDefault('_format', 'html');
	}

	public function setScheme($scheme)
	{
		return $this->setRequirement('_scheme', $scheme);
	}

	public function setMethod($method)
	{
		return $this->setRequirement('_method', $method);
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
		if (!is_array($params)) {
			$params = array($params);
		}
		foreach ($params as $param) {
			$this->setRequirement($param, '.*');
		}

		return $this;
	}
}