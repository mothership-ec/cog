<?php

namespace Message\Cog\ImageResize;

class Controller extends \Message\Cog\Controller\Controller
{
	public function index($url)
	{
		try {
			$resizer = $this->get('image.resize');
			$saved = $resizer->resize($url);	
		} catch(\Exception $e) {
			throw $this->createNotFoundException($e->getMessage());
		}

		return new \Symfony\Component\HttpFoundation\BinaryFileResponse($saved->realpath());
	}
}