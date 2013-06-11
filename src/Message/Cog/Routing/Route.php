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

	/**
	 * Enables CSRF protection for this route. When enabled a hash must be passed
	 * in as one of the parameters in the URL.
	 *
	 * @param  string $parameterName The name of the parameter to treat as a hash
	 *
	 * @return Route 	Returns $this for chainability
	 */
	public function enableCsrf($parameterName)
	{
		return $this->setDefault(self::CSRF_ATTRIBUTE_NAME, $parameterName);
	}

	/**
	 * Disables CSRF protection (if it was enabled with enableCsrf in the past)
	 *
	 * @return Route Returns $this for chainability
	 */
	public function disableCsrf()
	{
		return $this->setDefault(self::CSRF_ATTRIBUTE_NAME, null);
	}

	/**
	 * Generate the CSRF token for this route.
	 *
	 * @param  string $routeName The name of the route as it's stored in the collection
	 * @param  array $params     The parameters required for this route
	 * @param  string $sessionId A unique session ID for the current user
	 * @param  string $pepper    An installation unique salt for more randomness / security
	 *
	 * @return string            The generated token as a 40 character hex string.
	 */
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

		return hash_hmac('sha1', implode('|', $parts), $pepper);
	}
}