<?php

namespace Message\Cog\Routing;

class Route extends \Symfony\Component\Routing\Route
{
	const CSRF_ATTRIBUTE_NAME = '_csrf';
	const CSRF_SECRET         = 'SECRETPEPPER';

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

	public function enableCsrf($parameterName)
	{
		return $this->setDefault(self::CSRF_ATTRIBUTE_NAME, $parameterName);
	}

	public function disableCsrf()
	{
		return $this->setDefault(self::CSRF_ATTRIBUTE_NAME, null);
	}

	public function getCsrfToken($routeName, $params, $sessionId, $pepper)
	{
		$csrfKey = $this->getDefault(self::CSRF_ATTRIBUTE_NAME);

		$parts = array(
			$routeName,
			$this->getDefault('_controller'),
			$sessionId,
		);

		foreach($params as $key => $param) {
			if(substr($key, 0, 1) !== '_' && $key !== $csrfKey) {
				$parts[] = $key.'=>'.$param;
			}
		}

	//	var_dump($parts, $pepper, $routeName, $csrfKey, hash_hmac('sha1', implode('|', $parts), $pepper));
	//	exit;

		return hash_hmac('sha1', implode('|', $parts), $pepper);
	}
}