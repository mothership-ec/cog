<?php

namespace Message\Cog\Templating\Twig\Extension;

use Message\Cog\Templating\Helper\Actions as ActionsHelper;

use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Provides integration with the HttpKernel component.
 *
 * @link https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Extension/HttpKernelExtension.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpKernel extends \Twig_Extension
{
	protected $_helper;

	/**
	 * Constructor.
	 *
	 * @param ActionsHelper $helper The Actions templating helper
	 */
	public function __construct(ActionsHelper $helper)
	{
		$this->_helper = $helper;
	}

	public function getFunctions()
	{
		return array(
			'render'     => new \Twig_Function_Method($this, 'renderFragment', array('is_safe' => array('html'))),
			'render_*'   => new \Twig_Function_Method($this, 'renderFragmentStrategy', array('is_safe' => array('html'))),
			'controller' => new \Twig_Function_Method($this, 'controller'),
		);
	}

	/**
	 * Renders a fragment.
	 *
	 * @see Message\Cog\Templating\Helper\Actions::render
	 *
	 * @param string|ControllerReference $uri  A URI as a string or a ControllerReference instance
	 * @param array                   $options An array of options
	 *
	 * @return string The fragment content
	 */
	public function renderFragment($uri, $options = array())
	{
		return $this->_helper->render($uri, $options);
	}

	/**
	 * Renders a fragment using a specific strategy.
	 *
	 * @see Message\Cog\Templating\Helper\Actions::render
	 *
	 * @param string                     $strategy A strategy name
	 * @param string|ControllerReference $uri      A URI as a string or a ControllerReference instance
	 * @param array                      $options  An array of options
	 *
	 * @return string The fragment content
	 */
	public function renderFragmentStrategy($strategy, $uri, $options = array())
	{
		$options['strategy'] = $strategy;

		return $this->_helper->render($uri, $options);
	}

	/**
	 * @see Message\Cog\Templating\Helper\Actions::controller
	 */
	public function controller($controller, $attributes = array(), $query = array())
	{
		return $this->_helper->controller($controller, $attributes, $query);
	}

	public function getName()
	{
		return 'http_kernel';
	}
}