<?php

namespace Message\Cog\HTTP\REST;

use Message\Cog\HTTP\Curl\Request as CurlRequest;
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
		return $this->_getCurlResponse($requestData, $params);
	}

	private function _getCurlResponse(RequestData $requestData, array $params)
	{
		$content = $this->_getContent($requestData, $params);
		$url = $requestData->getUrl() . '?' . implode('&', $this->_parseParams($params));
		$curlRequest = new CurlRequest($this->_getContent($requestData, $params), $url, $requestData->getMethod(), $this->_getHeaders($content));

		return $curlRequest->send();
	}

	private function _getContent(RequestData $requestData)
	{
		$data = $requestData->getData();
		$data = ['xml' => urlencode($this->_serializer->serialize($data))];

		$content = '';

		foreach ($data as $key => $value) {
			$content .= $key . '=' . $value . '&';
		}

		$content = rtrim($content, '&');

		return $content;
	}

	private function _getHeaders($content)
	{
		return [
			"Content-Type: application/x-www-form-urlencoded",
			"Content-Length: " . strlen($content),
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