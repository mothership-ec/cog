<?php

namespace Message\Cog\ImageResize\Templating;

use Message\Cog\ImageResize\Resize;
use Message\Cog\ImageResize\ResizableInterface;
use Message\Cog\HTTP\Response;

/**
 * Provides integration of the ImageResize component with Twig.
 *
 * @author James Moss <james@message.co.uk>
 * @author Iris Schaffer <iris@message.co.uk>
 */
class TwigExtension extends \Twig_Extension
{
	protected $_baseDir;
	protected $_resize;

	public function __construct($baseDir, Resize $resize)
	{
		$this->_baseDir = $baseDir;
		$this->_resize  = $resize;
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			'getResizedUrl'    => new \Twig_Function_Method($this, 'getResizedUrl'),
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

	public function getResizedUrl($file, $width, $height)
	{
		$this->_checkFileType($file);

		$url = ($file ? $file->getUrl() : '');
		return $this->_resize->generateUrl($url, $width, $height);
	}

	/**
	 * Function which renders the image-tag with set width, height, src and alt-attributes.
	 * More attributes can passed in through the $attributes-array.
	 * If attributes['alt'] is defined, it will be used in the tag instead of $file->getAltText()
	 *
	 * @param \Twig_Environment		$environment 	Twig Environment needed to render image-tag
	 * @param ResizableInterface	$file 			File to be resized
	 * @param mixed					$width 			New width
	 * @param mixed					$height 		New height
	 * @param array 				$attributes 	Additional attributes to be used in the image-tag
	 */
	public function getResizedImageTag(\Twig_Environment $environment, $file, $width, $height, $attributes = array())
	{
		$this->_checkFileType($file);

		$url = $this->getResizedUrl($file, $width, $height);
		$alt = (array_key_exists('alt', $attributes) ? $attributes['alt'] : ($file ? $file->getAltText() : ""));

		$resize = $this->_resize;
		if ($width == $resize::AUTO_KEYWORD or $height == $resize::AUTO_KEYWORD) {
			list($sw, $sh) = getimagesize($this->_baseDir . $file->getUrl());

			if ($width == $resize::AUTO_KEYWORD)  $width = $sw;
			if ($height == $resize::AUTO_KEYWORD) $height = $sh;
		}

		return $environment->render('Message:Cog::image-resize:image',
			array(
				'url'		 => $url,
				'width'		 => $width,
				'height' 	 => $height,
				'altText' 	 => $alt,
				'attributes' => $attributes
			)
		);
	}

	protected function _checkFileType($file)
	{
		if(!($file instanceof ResizableInterface || is_null($file) || false === $file)) {
			throw new \InvalidArgumentException("$file must either be an instance of ResizableInterface or null!");
		}
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