<?php

namespace Message\Cog\ImageResize;

use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\Filesystem;

use Imagine\Image\ImagineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Resize
 *
 * Resizes a publically accessible image based on URL.
 */
class Resize
{
	const DIMENSION_AUTO = 9999;
	const AUTO_KEYWORD   = 'AUTO';

	protected $_imagine;
	protected $_generator;
	protected $_salt;
	protected $_defaultQuality = 90;
	protected $_defaultImagePath;
	protected $_publicDirectory = 'cog://public'; // replace by either contant or inject??

	/**
	 * Constructor
	 *
	 * @param ImagineInterface      $imagine   An instance of the Imagine library
	 * @param UrlGeneratorInterface $generator An instance of the URL generator
	 * @param string                $routeName The route for the controller where this class is used
	 * @param string                $salt      A random string used to prevent people from
	 *                                         creating arbritary sized images.
	 */
	public function __construct(ImagineInterface $imagine, UrlGeneratorInterface $generator, $routeName, $salt, $defaultImagePath)
	{
		$this->_imagine  		 = $imagine;
		$this->_generator 		 = $generator;
		// The url param is mandatory for this route so we pass it in then strip it off with substr()
		$this->_cacheDir  		 =  substr($this->_generator->generate($routeName, array('url' => '-')), 0, -2);
		$this->_cachePath 		 = $this->_publicDirectory.$this->_cacheDir;
		$this->_salt      		 = $salt;
		$this->_defaultImagePath = $defaultImagePath;
	}

	/**
	 * Gets the directory where resized images get stored
	 *
	 * @return string The path to the resize directory.
	 */
	public function getCachePath()
	{
		return $this->_cachePath;
	}

	/**
	 * Resize a public image based on it's URL
	 *
	 * @param  string $url The image to resize
	 *
	 * @return File        An object representing the newly resized image.
	 */
	public function resize($url)
	{
		$url = '/'.ltrim($url, '/');

		if(!file_exists($this->_cachePath) || !is_writeable($this->_cachePath)) {
			throw new \RuntimeException('Cache directory does not exist or is not writeable.');
		}

		// dont cache an already cached file
		if(substr($url, 0, strlen($this->_cacheDir.'/')) === $this->_cacheDir.'/') {
			throw new \RuntimeException('Files inside the cache cannot be cached again.');
		}

		// parse the parameters. Matches URLs like
		// - /resize/files/test_800xAUTO-b151b7.jpg
		// - /resize/files/another_image-white_400x300-72acf5a.gif
		if(!preg_match("/(.*)_(.+)\-([a-f0-9]{6})(\.[a-zA-Z]+)$/u", $url, $matches)) {
			throw new Exception\BadParameters('Not a valid resize path.');
		}

		list($match, $path, $paramString, $hash, $ext) = $matches;

		$this->_validateHash($path, $paramString, $hash, $ext);
		$params = $this->_parseParams($paramString);

		// lets generate an image!
		$original = new File($this->_publicDirectory.$path.$ext);

		// ensure original exists
		if(!file_exists($original) || !is_file($original)) {
			throw new Exception\NotFound('Neither the original file nor the default-image exist.');
		}

		// make sure the target dir exists and we can write to it.
		$saved    = new File($this->_cachePath.$url);
		$savedRaw = new File($saved->getRealPath());
		$fs       = new Filesystem;

		$fs->mkdir($savedRaw->getPath(), 0777);

		if($params['width'] === self::DIMENSION_AUTO || $params['height'] === self::DIMENSION_AUTO) {
			$mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
		} else {
			$mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
		}

		$box = new \Imagine\Image\Box($params['width'], $params['height']);
		$image = $this->_imagine
			->open($original->getPathname())
			->thumbnail($box, $mode)
			->save($saved->getRealPath(), array('quality' => $this->_defaultQuality));

		$fs->chmod($saved->getRealPath(), 0777);

		return $saved;
	}

	/**
	 * Generate a URL to be fed into resize()
	 *
	 * @param  string   $url      A publically accessible image.
	 * @param  int|null $width    The width of the image to generate (in pixels). If null
	 *                            it is calculated based on height.
	 * @param  int|null $height   The height of the image to generate (in pixels). If null
	 *                            it is calculated based on width.
	 *
	 * @return string 	The URL that the resized image can be accessed at.
	 */
	public function generateUrl($url, $width, $height)
	{
		$original = new File($this->_publicDirectory.$url);
		if(!file_exists($original) || !is_file($original)) {
			$url = $this->_defaultImagePath;
		}

		$url = ltrim($url, '/');

		if(is_null($width)) {
			$width = self::AUTO_KEYWORD;
		}

		if(is_null($height)) {
			$height = self::AUTO_KEYWORD;
		}

		// build the full url
		$parts = pathinfo($url);
		$path = $this->_cacheDir.'/'.$parts['dirname'].'/'.$parts['filename'];
		$ext  = '.'.$parts['extension'];
		$params = $width.'x'.$height;

		$hash = $this->_makeHash($path, $ext, $params);

		return $this->_cacheDir.'/'.$parts['dirname'].'/'.rawurlencode($parts['filename']).'_'.$params.'-'.$hash.$ext;
	}

	/**
	 * Sets the default image quality when saving an image
	 *
	 * @param int $quality A value between 0 and 100 representing the quality level.
	 */
	public function setDefaultQuality($quality)
	{
		$this->_defaultQuality = (int)$quality;
	}

	/**
	 * Parse and validate the parameters from a string into an array.
	 *
	 * @param  string $paramString The parameters to be parsed
	 *
	 * @return array               The parsed parameters.
	 */
	protected function _parseParams($paramString)
	{
		$params = explode('-', $paramString);

		// Ensure we specify a size.
		// This matches sizes like 600x400 or 900xAUTO or AUTOx300
		$regex = "/^([0-9]+?|".preg_quote(self::AUTO_KEYWORD).")x([0-9]+?|".preg_quote(self::AUTO_KEYWORD).")$/u";
		if(!preg_match($regex, $params[0], $matches)) {
			throw new \Exception('Bad dimensions.');
		}

		$width  = $matches[1] == self::AUTO_KEYWORD ? self::DIMENSION_AUTO : (int)$matches[1];
		$height = $matches[2] == self::AUTO_KEYWORD ? self::DIMENSION_AUTO : (int)$matches[2];

		if($width === self::DIMENSION_AUTO && $height === self::DIMENSION_AUTO) {
			throw new Exception\BadParameters('Both dimensions cannot be auto.');
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Check if the hash for a URL is valid.
	 *
	 * This is used to stop people from modifying the URL and creating arbitrarily
	 * sized images.
	 *
	 * @param  string $path   The path to the image
	 * @param  string $params The parameters for the image.
	 * @param  string $hash   The hash to compare to.
	 * @param  string $ext    The file extension.
	 *
	 * @return void
	 */
	protected function _validateHash($path, $params, $hash, $ext)
	{
		if($this->_makeHash($path, $ext, $params) !== $hash) {
			throw new Exception\BadParameters('Bad hash.');
		}
	}

	/**
	 * Create a basic security hash.
	 *
	 * @param  string $path   The path to the image
	 * @param  string $ext    The extension of the image
	 * @param  string $params The parameters for the image
	 *
	 * @return string         A 6 character hash.
	 */
	public function _makeHash($path, $ext, $params)
	{
		$path = basename($path);

		return substr(md5($path.$ext.'|'.$params.'|'.$this->_salt), 0, 6);
	}
}