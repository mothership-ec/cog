<?php

namespace Message\Cog\Routing;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Empty wrapper around Symfony's `UrlGenerator` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
	protected $_session;
	protected $_pepper;

	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		// Check if this URL needs a CSRF token.
		if (null !== $route = $this->routes->get($name)) {
			if($csrfField = $route->getDefault(Route::CSRF_ATTRIBUTE_NAME)) {
				$parameters[$csrfField] = $route->getCsrfToken(
					$name,
					$parameters,
					$this->_session->getId(),
					$this->_pepper
				);
			}
		}

		return parent::generate($name, $parameters, $referenceType);
	}

	public function setCsrfSecrets(SessionInterface $session, $pepper)
	{
		$this->_session   = $session;
		$this->_pepper    = $pepper;
	}
}