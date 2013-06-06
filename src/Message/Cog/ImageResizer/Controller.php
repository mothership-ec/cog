<?php

namespace Message\Cog\ImageResizer;

use Message\Cog\Filesystem\File;

class Controller extends \Message\Cog\Controller\Controller
{
	public function index($url)
	{
		// check to see if this item already exists
		$url = '/'.$url;
		$cacheDir = 'cog://public/cache';
		if(file_exists($cacheDir.$url)) {
			return $this->redirect('/cache'.$url, 301);
		}

		// http://james:8001/cache/file/2013-04-19%2006.23.01_500x500-b151b7.jpg

		// parse the parameters
		if(!preg_match("/(.*)_(.+)-([a-f0-9]{6})(\\.?.*?)??$/u", $url, $matches)) {
			throw $this->createNotFoundException('Not a valid resize path.');
		}

		list($match, $path, $paramString, $hash, $ext) = $matches;

		$this->_validateHash($path, $paramString, $hash, $ext);
		$params = $this->_parseParams($paramString);

		// lets generate an image!
		$original = new File('cog://public'.$path.$ext);

		// ensure original exists
		if(!file_exists($original)) {
			throw $this->createNotFoundException('The original file does not exist.');
		}

		// make sure the target dir exists
		$info = pathinfo($cacheDir.$url);
		$fs = new \Message\Cog\Filesystem\Filesystem;
		$fs->mkdir($info['dirname'], 0777);
		// @todo make this work
		//$fs->chmod($info['dirname'], 0777, 0777, true);

		$saved = new File($cacheDir.$url);

		$imagine = new \Imagine\Gd\Imagine();
		$image = $imagine
			->open($original->getPathname())
			->thumbnail(new \Imagine\Image\Box($params['width'], $params['height']), \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND)
			->save($saved->realpath());

		return $this->redirect($saved->getPublicUrl());
	}

	public function _validateHash($path, $params, $hash, $ext)
	{
		$check = substr(md5($path.$ext.'|'.$params), 0, 6);

		if($check !== $hash) {
			//throw $this->createNotFoundException('Bad hash.');
		}
	}

	public function _parseParams($paramString)
	{
		$params = explode('-', $paramString);

		$return = array();

		// ensure we specify a size
		if(!preg_match("/([0-9]+?|AUTO)x([0-9]+?|AUTO)/u", $params[0], $matches)) {
			throw $this->createNotFoundException('Bad dimensions.');
		}

		$width = $matches[0];
		$height = $matches[1];

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}
}