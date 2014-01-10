<?php

namespace Message\Cog\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Templating helper for generating URLs from route names.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Routing extends Helper
{
	protected $_generator;

	/**
	 * Constructor.
	 *
	 * @param UrlGeneratorInterface $generator A URL generator
	 */
	public function __construct(UrlGeneratorInterface $generator)
	{
		$this->_generator = $generator;
	}

	/**
	 * Generates a URL from the given parameters.
	 *
	 * @see UrlGeneratorInterface::generate
	 *
	 * @param string         $name          The name of the route
	 * @param mixed          $parameters    An array of parameters
	 * @param boolean|string $referenceType The type of reference (one of the
	 *                                      constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 */
	public function generate($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->_generator->generate($name, $parameters, $absolute);
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return string The canonical name
	 */
	public function getName()
	{
		return 'routing';
	}
}