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
		$schemes = (array) $scheme;

		return $this->setSchemes($schemes);
	}

	public function setMethod($method)
	{
		$methods = (array) $method;

		return $this->setMethods($methods);
	}

	public function setFormat($format)
	{
		return $this->setDefault('_format', $format);
	}
}