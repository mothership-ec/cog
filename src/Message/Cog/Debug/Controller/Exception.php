<?php

namespace Message\Cog\Debug\Controller;

use Message\Cog\Controller\Controller;

class Exception extends Controller
{
	public function viewException($exception)
	{
		return $this->render('Message:Cog:Debug::exception', array(
			'exception' => $exception,
		));
	}
}