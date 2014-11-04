<?php

namespace Message\Cog\HTTP\Curl;

class Response
{
	const HTTP_CODE = 'http_code';

	private $_httpResponse;
	private $_info;

	public function __construct($httpResponse, array $info)
	{
		$this->setHttpResponse($httpResponse);
		$this->setInfo($info);
	}

	/**
	 * @param string $httpResponse
	 * @throws \InvalidArgumentException
	 *
	 * @return Response         return $this for chainability
	 */
	public function setHttpResponse($httpResponse)
	{
		if (!is_string($httpResponse)) {
			throw new \InvalidArgumentException('HTTP Response must be a string, ' . gettype($httpResponse) . ' given');
		}

		$this->_httpResponse = $httpResponse;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHttpResponse()
	{
		return $this->_httpResponse;
	}

	/**
	 * @param array $info
	 *
	 * @return Response         return $this for chainability
	 */
	public function setInfo(array $info)
	{
		$this->_info = $info;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->_info;
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->_info[self::HTTP_CODE];
	}

	public function get($key)
	{
		if (!is_string($key)) {
			throw new \InvalidArgumentException('Key must be a string, ' . gettype($key) . ' given');
		}
		if (!array_key_exists($key, $this->_info)) {
			throw new \LogicException('`' . $key . '` does not exist');
		}

		return $this->_info[$key];
	}
}