<?php

namespace Message\Cog\ImageResize;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller extends \Message\Cog\Controller\Controller
{
	public function index($url)
	{
		try {
			$resizer = $this->get('image.resize');
			$saved = $resizer->resize($url);	
		} 
		catch(Exception\NotFound $e) {
			throw $this->createNotFoundException($e->getMessage());
		}
		catch(Exception\BadParameters $e) {
			throw new BadRequestHttpException($e->getMessage());
		}
		catch(\Exception $e) {
			throw new HttpException(500, $e->getMessage());
		}

		return new BinaryFileResponse($saved->realpath());
	}
}