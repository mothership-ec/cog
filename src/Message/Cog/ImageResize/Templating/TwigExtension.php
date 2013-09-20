<?php

namespace Message\Cog\ImageResize\Templating;

use Message\Cog\ImageResize\Resize;
use Message\Cog\ImageResize\ResizableInterface;
use Message\Cog\HTTP\Response;

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
			'getResizedUri'    => new \Twig_Function_Method($this, 'getResizedUri'),
			'getResizedImage'  => new \Twig_Function_Method(
				$this,
				'getResizedImageTag',
				array(
					'needs_environment' => true,
					'is_safe' => array('html'),
				)
			),
		);
	}

	public function getResizedUri(ResizableInterface $file, $width, $height)
	{
		return $this->_resize->generateUrl($file->getUrl(), $width, $height);
	}

	public function getResizedImageTag(\Twig_Environment $environment, ResizableInterface $file, $width, $height, $attributes = array())
	{
		$url = $this->getResizedUri($file, $width, $height);

		return $environment->render('Message:Cog::image-resize:image',
			array(
				'url'		 => $url,
				'width'		 => $width,
				'height' 	 => $height,
				'altText' 	 => $file->getAltText(),
				'attributes' => $attributes
			));
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