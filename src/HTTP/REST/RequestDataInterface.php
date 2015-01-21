<?php

namespace Message\Cog\HTTP\REST;

interface RequestDataInterface
{
	/**
	 * Get HTTP method
	 *
	 * @return string
	 */
	public function getMethod();

	/**
	 * Set HTTP method
	 *
	 * @param $method string
	 */
	public function setMethod($method);

	/**
	 * Get URL to send request to
	 *
	 * @return string
	 */
	public function getUrl();

	/**
	 * Set URL to send request to
	 *
	 * @param $url
	 */
	public function setUrl($url);

	/**
	 * Get raw data to send to API
	 *
	 * @return array
	 */
	public function getData();

	/**
	 * Set raw data to send to API
	 *
	 * @param array $data
	 */
	public function setData(array $data);
}