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

		$ch = curl_init();

		$secret = $params['consumer_secret'];
		unset($params['consumer_secret']);

		$oauth = new \OAuth($params['oauth_consumer_key'], $secret);
		$oauth->setAuthType(OAUTH_AUTH_TYPE_URI);
		$oauth->enableDebug();

		de($oauth->getRequestToken('https://api.xero.com/oauth/RequestToken'));

//		$signature = sha1($secret);
		$data = $this->_getContent($requestData, $params);

		openssl_pkey_export(['WQSQ4BUMTMWNIG3AYJXJVDLYO0TDVE', 'L9WQ0RA56TJZOSS5RR57KHN8P8EO7M'], $key);
		openssl_sign($data, $signature, sha1($secret));

		$params['oauth_signature'] = $signature;


		d('https://api.xero.com/oauth/RequestToken?' . $this->_getContent($requestData, $params));
		curl_setopt($ch, CURLOPT_URL, 'https://api.xero.com/oauth/RequestToken?' . $this->_getContent($requestData, $params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$response = curl_exec($ch);
		$code = curl_getinfo($ch);
		curl_close($ch);

		return [$response, $code];


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