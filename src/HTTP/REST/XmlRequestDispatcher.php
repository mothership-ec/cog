<?php

namespace Message\Cog\HTTP\REST;

use Message\Cog\HTTP\Request;
use Message\Cog\Serialization\ArrayToXml;

use Symfony\Component\HttpKernel\HttpKernelInterface;

class XmlRequestDispatcher implements RequestDispatcherInterface
{
	const CONTENT_TYPE = 'test/xml';

	/**
	 * @var \Message\Cog\Serialization\ArrayToXml
	 */
	private $_serializer;

	public function __construct(HttpKernelInterface $kernel, ArrayToXml $serializer)
	{
		$this->_kernel = $kernel;
		$this->_serializer = $serializer;
	}

	public function getName()
	{
		return 'xml';
	}

	public function sendRequestData(RequestData $requestData)
	{
		$request = $this->_getRequestObject($requestData);

		return $this->_kernel->handle($request);
	}

	private function _getRequestObject(RequestData $requestData)
	{
		$request = new Request;
		$request->initialize(
			$requestData->getUrl(),
			$requestData->getMethod(),
			[],
			[],
			[],
			[],
			$this->_getContent($requestData)
		);

		$request->headers->set('Content-Type', self::CONTENT_TYPE);

		return $request;
	}

	private function _getContent(RequestData $requestData)
	{
		return $this->_serializer->serialize($requestData->getData());
	}
}