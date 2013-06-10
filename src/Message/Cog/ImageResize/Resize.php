<?php

namespace Message\Cog\ImageResize;

use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\Filesystem;

use Imagine\Image\ImagineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Resize
{
	const DIMENSION_AUTO = 9999;
	const AUTO_KEYWORD   = 'AUTO';

	protected $_imagine;
	protected $_generator;
	protected $_salt;

	public function __construct(ImagineInterface $imagine, UrlGeneratorInterface $generator, $routeName, $salt)
	{
		$this->_imagine   = $imagine;
		$this->_generator = $generator;
		// The url param is mandatory for this route so we pass it in then strip it off with substr()
		$this->_cacheDir  =  substr($this->_generator->generate($routeName, array('url' => '-')), 0, -2);
		$this->_cachePath = 'cog://public'.$this->_cacheDir;
		$this->_salt = $salt;
	}

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
		$original = new File('cog://public'.$path.$ext);

		// ensure original exists
		if(!file_exists($original) || !is_file($original)) {
			throw new Exception\NotFound('The original file does not exist or is a directory.');
		}

		// make sure the target dir exists and we can write to it.
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

	protected function _parseParams($paramString)
	{
		$params = explode('-', $paramString);

		// ensure we specify a size
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

	protected function _validateHash($path, $params, $hash, $ext)
	{
		if($this->_makeHash($path, $ext, $params) !== $hash) {
			throw new Exception\BadParameters('Bad hash.');
		}
	}

	public function _makeHash($path, $ext, $params)
	{
		$path = basename($path);
		
		return substr(md5($path.$ext.'|'.$params.'|'.$this->_salt), 0, 6);
	}
}