<?php

namespace Message\Cog\Templating\Helper;

use Message\Cog\ImageResize\Resize;

use Symfony\Component\Templating\Helper\Helper;

/**
 * Templating helper for generating URLs from route names.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ImageResize extends Helper
{
	protected $_resize;

	/**
	 * Constructor.
	 *
	 * @param Resize $resize An image resize instance
	 */
	public function __construct(Resize $resize)
	{
		$this->_resize = $resize;
	}

	/**
	 * Generates a URL to a resized image.
	 *
	 * @see Resize::generateUrl
	 *
	 * @param string         $url      The public path to the file to resize
	 * @param int|null       $width    The width of the image to create
	 * @param int|null       $height   The height of the image to create
	 * 
	 * @return string The generated URL
	 */
	public function generateUrl($url, $width, $height)
	{
		return $this->_resize->generateUrl($url, $width, $height);
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return string The canonical name
	 */
	public function getName()
	{
		return 'imageresize';
	}
}