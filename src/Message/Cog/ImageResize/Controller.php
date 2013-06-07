<?php

namespace Message\Cog\ImageResize;

class Controller extends \Message\Cog\Controller\Controller
{
	public function index($url)
	{
		file_put_contents('cog://tmp/mossdebug.log', print_r(array($url), true), FILE_APPEND);

		try {
			$resizer = $this->get('image.resize');
			$saved = $resizer->resize($url);	
		} catch(\Exception $e) {
			throw $this->createNotFoundException($e->getMessage());
		}

		file_put_contents('cog://tmp/mossdebug.log', print_r(array($url, $saved), true), FILE_APPEND);

		return new \Symfony\Component\HttpFoundation\BinaryFileResponse($saved->realpath());
	}
}