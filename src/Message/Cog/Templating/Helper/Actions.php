<?php

namespace Message\Cog\Templating\Helper;

use Message\Cog\ReferenceParserInterface;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Templating helper for executing actions such as sub-requests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Actions extends Helper
{
	protected $_handler;
	protected $_referenceParser;

	/**
	 * Constructor.
	 *
	 * @param FragmentHandler $handler The fragment handler instance
	 */
	public function __construct(FragmentHandler $handler, ReferenceParserInterface $referenceParser)
	{
		$this->_handler         = $handler;
		$this->_referenceParser = $referenceParser;
	}

	/**
	 * Returns the fragment content for a given URI.
	 *
	 * @see Symfony\Component\HttpKernel\Fragment\FragmentHandler::render()
	 *
	 * @param string $uri     A URI
	 * @param array  $options An array of options
	 *
	 * @return string The fragment content
	 */
	public function render($uri, array $options = array())
	{
		$strategy = isset($options['strategy']) ? $options['strategy'] : 'inline';
		unset($options['strategy']);

		return $this->_handler->render($uri, $strategy, $options);
	}

	/**
	 * Get a `ControllerReference` instance for a given controller. This is
	 * often used in combination with `render()`.
	 *
	 * @param  string $controller Controller reference
	 * @param  array  $attributes Attributes
	 * @param  array  $query	  Query variables
	 *
	 * @return ControllerReference
	 */
	public function controller($controller, $attributes = array(), $query = array())
	{
		$parsed = $this->_referenceParser->parse($controller);

		return new ControllerReference($parsed->getSymfonyLogicalControllerName(), $attributes, $query);
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return string The canonical name
	 */
	public function getName()
	{
		return 'actions';
	}
}
