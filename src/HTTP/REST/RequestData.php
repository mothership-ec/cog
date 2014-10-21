<?php

namespace Message\Cog\HTTP\REST;

use Message\Cog\HTTP\Methods;

class RequestData
{
	/**
	 * @var string
	 */
	private $_method;

	/**
	 * @var string
	 */
	private $_url;

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param string $method
	 *
	 * @throws \InvalidArgumentException   throws exception if method is not a string
	 * @throws \LogicException             throws exception if method is not a valid HTTP method
	 *
	 * @return RequestData                 return $this for chainability
	 */
	public function setMethod($method)
	{
		if (!is_string($method)) {
			throw new \InvalidArgumentException('Method must be a string');
		}

		if (!in_array($method, Methods::get())) {
			throw new \LogicException('`' . $method . '` is not a valid HTTP method');
		}

		$this->_method = $method;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * @param string $url
	 *
	 * @throws \InvalidArgumentException   throws exception if url is not a string
	 * @throws \LogicException             throws exception if url is not valid
	 *
	 * @return RequestData                 return $this for chainability
	 */
	public function setUrl($url)
	{
		if (!is_string($url)) {
			throw new \InvalidArgumentException('URL must be a string');
		}

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \LogicException('`' . $url . '` is not a valid URL');
		}

		$this->_url = $url;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->_url;
	}

	/**
	 * @param array $data
	 *
	 * @return RequestData         return $this for chainability
	 */
	public function setData($data)
	{
		$this->_data = $data;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

}