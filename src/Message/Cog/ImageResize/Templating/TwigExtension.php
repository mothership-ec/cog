<?php

namespace Message\Cog\ImageResize\Templating;

use Message\Cog\ImageResize\Resize;
use Message\Cog\ImageResize\ResizableInterface;

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
			'resize'  		   => new \Twig_Function_Method($this, 'getResizeUrl'),
			'resizeAndRender'  => new \Twig_Function_Method($this, 'getResizeUrlAndRender'),
		);
	}

	public function getResizeUrl(ResizableInterface $file, $width, $height)
	{
		return $this->_resize->generateUrl($file->getUrl(), $width, $height);
	}

	public function getResizeUrlAndRender(ResizableInterface $file, $width, $height, $attributes)
	{
		$url = $this->getResizeUrl($file, $width, $height);

		$attributeString = "";
		foreach($attributes as $attribute => $value) {
			$attributeString .= sprintf('%s="%s" ', $attribute, $value);
		}

		return sprintf('<img src="%s" width="%d" height="%d" alt="%s" %s>', $url, $width, $height, $file->getAltText(), $attributeString);
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