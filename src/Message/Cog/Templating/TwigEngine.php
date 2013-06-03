<?php

namespace Message\Cog\Templating;

use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

use Twig_Environment;
use Twig_TemplateInterface;
use Twig_Template;
use Twig_Error_Loader;

use InvalidArgumentException;

/**
 * Twig templating engine. This class is essentially a bridge between Cog's
 * templating component and the Twig library.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TwigEngine implements EngineInterface, StreamingEngineInterface
{
	protected $_twig;
	protected $_parser;

	/**
	 * Constructor.
	 *
	 * @param Twig_Environment            $twig   The twig environment instance
	 * @param TemplateNameParserInterface $parser View name parser to use
	 */
	public function __construct(Twig_Environment $twig, TemplateNameParserInterface $parser)
	{
		$this->_twig   = $twig;
		$this->_parser = $parser;
	}

	/**
	 * Renders a view
	 *
	 * @param  mixed $name       A view reference
	 * @param  array $parameters An array of parameters to pass to the view
	 *
	 * @return string            The evaluated view as a string
	 */
	public function render($name, array $parameters = array())
	{
		return $this->_load($name)->render($parameters);
	}

	/**
	 * Stream a view.
	 *
	 * @param mixed $name       A view reference
	 * @param array $parameters An array of parameters to pass to the view
	 */
	public function stream($name, array $parameters = array())
	{
		$this->_load($name)->display($parameters);
	}

	/**
	 * Check if a given view exists.
	 *
	 * @param  string $name A view reference
	 *
	 * @return boolean      True if the view exists, false otherwise
	 */
	public function exists($name)
	{
		try {
			$this->_load($name);
		}
		catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Check if this rendering engine supports a given view.
	 *
	 * @param  mixed $name A view reference
	 *
	 * @return boolean     True if this rendering engine supports the view
	 */
	public function supports($name)
	{
		if ($name instanceof Twig_Template) {
			return true;
		}

		return 'twig' === $this->_parser->parse($name)->get('engine');
	}

	/**
	 * Load a view.
	 *
	 * @param  mixed $name            A view reference or instance of Twig_Template
	 *
	 * @return Twig_TemplateInterface A twig template object
	 *
	 * @throws InvalidArgumentException If the view does not exist or cannot be loaded
	 */
	protected function _load($name)
	{
		if ($name instanceof Twig_Template) {
			return $name;
		}

		try {
			return $this->_twig->loadTemplate($name);
		}
		catch (Twig_Error_Loader $e) {
			throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
		}
	}
}