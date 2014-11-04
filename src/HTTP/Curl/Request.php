<?php

namespace Message\Cog\HTTP\Curl;


class Request
{
	private $_content;
	private $_url;
	private $_method;
	private $_headers;

	public function __construct($content, $url, $method, $headers)
	{
		$this->setContent($content);
		$this->setUrl($url);
		$this->setMethod($method);
		$this->setHeaders($headers);
	}

	/**
	 * @param string $content
	 * @throws \InvalidArgumentException
	 *
	 * @return Request         return $this for chainability
	 */
	public function setContent($content)
	{
		if (!is_string($content)) {
			throw new \InvalidArgumentException('Content must be a string, ' . gettype($content) . ' given');
		}

		$this->_content = $content;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * @param string $url
	 * @throws \InvalidArgumentException
	 *
	 * @return Request         return $this for chainability
	 */
	public function setUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException('Not a valid URL!');
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
	 * @param mixed $method
	 *
	 * @return Request         return $this for chainability
	 */
	public function setMethod($method)
	{
		$this->_method = $method;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * @param array $headers
	 *
	 * @return Request         return $this for chainability
	 */
	public function setHeaders(array $headers)
	{
		$this->_headers = $headers;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	public function send()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getContent());

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if (false === $response) {
			throw new CurlException(curl_error($ch));
		}

		return new Response($response, $info);
	}
}

