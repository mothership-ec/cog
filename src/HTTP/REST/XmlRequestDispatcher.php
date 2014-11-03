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

	public function sendRequestData(RequestData $requestData, array $params = [])
	{
		$request = $this->_getRequestObject($requestData, $params);

		return $this->_kernel->handle($request);
	}

	private function _getRequestObject(RequestData $requestData, array $params)
	{
		de($this->_getCurlResponse($requestData, $params));

		$request = Request::create(
			$requestData->getUrl(),
			$requestData->getMethod(),
			[], // $parameters
			[], // $cookies
			[], // $files
			[], // $server
			$this->_getContent($requestData, $params)
		);

		$request->headers->set('Content-Type', self::CONTENT_TYPE);

		return $request;
	}

	private function _getContent(RequestData $requestData, array $params)
	{
		$params = $this->_parseParams($params);
//		$params[] = 'xml=' . urlencode($this->_serializer->serialize($requestData->getData()));
		$content = implode('&', $params);

		return $content;
	}

	private function _getCurlResponse(RequestData $requestData, $params)
	{
		$content = $this->_getContent($requestData, $params);
		$headers = $this->_getHeaders($content);


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestData->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);

		switch ($requestData->getMethod()) {
			case 'POST' :
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
				break;
		}

		$response = curl_exec($ch);
		$code = curl_getinfo($ch);
		curl_close($ch);

		return [$response, $code];
	}

	private function _getHeaders($content)
	{
		return [
			"Content-type: application/x-www-form-urlencoded",
			"Content-length: " . strlen($content),
			"Connection: close"
		];
	}

	private function _parseParams(array $params)
	{
		$data = [];

		foreach ($params as $key => $value) {
			if (is_array($value) || is_object($value)) {
				throw new \LogicException('Parameter array must be one dimensional');
			}

			$data[] = urlencode($key) . '=' . rawurlencode($value) . '';
		}

		return $data;
	}
}