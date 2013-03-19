<?php

namespace Message\Cog\Templating;

use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

use Twig_Environment;
use Twig_Template;
use Twig_Error_Loader;

use InvalidArgumentException;

class TwigEngine implements EngineInterface, StreamingEngineInterface
{
	protected $_twig;
	protected $_parser;

	public function __construct(Twig_Environment $twig, TemplateNameParserInterface $parser)
	{
		$this->_twig   = $twig;
		$this->_parser = $parser;
	}

	public function render($name, array $parameters = array())
	{
		return $this->_load($name)->render($parameters);
	}

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

	public function supports($name)
	{
		if ($name instanceof \Twig_Template) {
			return true;
		}

		return 'twig' === $this->_parser->parse($name)->get('engine');
	}

	public function stream($name, array $parameters = array())
	{
		$this->_load($name)->display($parameters);
	}

	protected function _load($name)
	{
		if ($name instanceof Twig_Template) {
			return $name;
		}

		try {
			return $this->environment->loadTemplate($name);
		}
		catch (Twig_Error_Loader $e) {
			throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
		}
	}
}