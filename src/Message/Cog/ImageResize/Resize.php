<?php

namespace Message\Cog\ImageResize;

use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\Filesystem;

use Imagine\Image\ImagineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Resize
{
	const ROUTE_NAME     = 'imageresize.cache';
	const DIMENSION_AUTO = 9999;
	const SALT           = 'rCWj^/P8HXMKAru6xX4;(YyP7HTvZutzzEAqoxM9M&Ip+K{{Lot 7g*ZiGR@g>-@';

	protected $_imagine;
	protected $_generator;

	public function __construct(ImagineInterface $imagine, UrlGeneratorInterface $generator)
	{
		$this->_imagine   = $imagine;
		$this->_generator = $generator;
		$this->_cacheDir  =  substr($this->_generator->generate(self::ROUTE_NAME, array('url' => '-')), 0, -2);
		$this->_cachePath = 'cog://public'.$this->_cacheDir;
	}

	public function resize($url)
	{
		$url = '/'.$url;
		
		if(!file_exists($this->_cachePath) || !is_writeable($this->_cachePath)) {
			throw new \Exception('Cache directory does not exist or is not writeable.');
		}

		// dont cache an already cached file
		if(substr($url, 0, strlen($this->_cacheDir.'/')) === $this->_cacheDir.'/') {
			throw new \Exception('Files inside the cache cannot be cached again.');
		}

		// parse the parameters. Matches URLs like
		// - /resize/files/test_800xAUTO-b151b7.jpg
		// - /resize/files/another_image-white_400x300-72acf5a.gif
		if(!preg_match("/(.*)_(.+)\-([a-f0-9]{6})(\.[a-zA-Z]+)$/u", $url, $matches)) {
			throw new \Exception('Not a valid resize path.');
		}

		list($match, $path, $paramString, $hash, $ext) = $matches;

		$this->_validateHash($path, $paramString, $hash, $ext);
		$params = $this->_parseParams($paramString);

		// lets generate an image!
		$original = new File('cog://public'.$path.$ext);

		// ensure original exists
		if(!file_exists($original) || !is_file($original)) {
			throw new \Exception('The original file does not exist or is a directory.');
		}

		// make sure the target dir exists and 
		$saved    = new File($this->_cachePath.$url);
		$savedRaw = new \SplFileInfo($saved->realpath());
		$fs       = new Filesystem;
		
		$fs->mkdir($savedRaw->getPath(), 0777);
	//	$fs->chmod($savedRaw->getPath(), 0777, 0777, true);

		if($params['width'] === self::DIMENSION_AUTO || $params['height'] === self::DIMENSION_AUTO) {
			$mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
		} else {
			$mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
		}

		$box = new \Imagine\Image\Box($params['width'], $params['height']);
		$image = $this->_imagine
			->open($original->getPathname())
			->thumbnail($box, $mode)
			->save($saved->realpath());

		return $saved;
	}

	public function generateUrl($url, $width, $height)
	{
		if(is_null($width)) {
			$width = self::DIMENSION_AUTO;
		}

		if(is_null($height)) {
			$height = self::DIMENSION_AUTO;
		}

		// build the full url
		$parts = pathinfo($url);
		$path = $parts['dirname'].'/'.$parts['filename'];
		$ext  = '.'.$parts['extension'];
		$params = $width.'x'.$height;

		$hash = $this->_makeHash($path, $ext, $params);

		return $path.'_'.$params.'-'.$hash.$ext;
	}

	protected function _parseParams($paramString)
	{
		$params = explode('-', $paramString);

		// ensure we specify a size
		if(!preg_match("/^([0-9]+?|AUTO)x([0-9]+?|AUTO)$/u", $params[0], $matches)) {
			throw new \Exception('Bad dimensions.');
		}

		$width  = $matches[1] == 'AUTO' ? self::DIMENSION_AUTO : (int)$matches[1];
		$height = $matches[2] == 'AUTO' ? self::DIMENSION_AUTO : (int)$matches[2];

		if($width === self::DIMENSION_AUTO && $height === self::DIMENSION_AUTO) {
			throw new \Exception('Both dimensions cannot be auto.');
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	protected function _validateHash($path, $params, $hash, $ext)
	{
		if($this->_makeHash($path, $ext, $params) !== $hash) {
			//throw new \Exception('Bad hash.');
		}
	}

	public function _makeHash($path, $ext, $params)
	{
		return substr(md5($path.$ext.'|'.$params.'|'.self::SALT), 0, 6);
	}
}