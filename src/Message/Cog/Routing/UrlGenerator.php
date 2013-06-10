<?php

namespace Message\Cog\Routing;

/**
 * Empty wrapper around Symfony's `UrlGenerator` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
	protected $_sessionID;
	protected $_pepper;

	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		// Check if this URL needs a CSRF token.
		if (null !== $route = $this->routes->get($name)) {
			if($csrfField = $route->getDefault(Route::CSRF_ATTRIBUTE_NAME)) {

				$parameters[$csrfField] = $route->getCsrfToken(
					$name,
					$parameters,
					$this->_sessionID,
					$this->_pepper
				);
			}
		}

		return parent::generate($name, $parameters, $referenceType);
	}

	public function setCsrfSecrets($sessionID, $pepper)
	{
		$this->_sessionID = $sessionID;
		$this->_pepper    = $pepper;
	}
}