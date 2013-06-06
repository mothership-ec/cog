<?php

namespace Message\Cog\Routing;

class Route extends \Symfony\Component\Routing\Route
{
	protected $_defaultFormat = 'html';

	public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
	{
		parent::__construct($pattern, $defaults, $requirements, $options);

		// Set default format to HTML if none is explicitly set
		if (!isset($defaults['_format'])) {
			$this->setFormat($this->_defaultFormat);
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
	 * Make a route parameter (or route parameters) optional.
	 *
	 * @param mixed $params Parameter name (or array of parameter names)
	 *
	 * @return Route 		Returns $this for chaining
	 */
	public function setOptional($params)
	{
		$params = (array) $params;

		foreach ($params as $param) {
			$this->setRequirement($param, '.*');
		}

		return $this;
	}
}