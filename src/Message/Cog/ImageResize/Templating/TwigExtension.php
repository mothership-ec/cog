<?php

namespace Message\Cog\ImageResize\Templating;

use Message\Cog\ImageResize\Resize;

/**
 * Provides integration of the ImageResize component with Twig.
 *
 * @author James Moss <james@message.co.uk>
 */
class TwigExtension extends \Twig_Extension
{
	protected $_resize;

	public function __construct(Resize $resize)
	{
		$this->_resize = $resize;
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			'resize'  => new \Twig_Function_Method($this, 'getResizeUrl'),
		);
	}

	public function getResizeUrl($url, $width, $height)
	{
		return $this->_resize->generateUrl($url, $width, $height);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'imageresize';
	}
}