<?php

namespace Message\Cog\HTTP\REST;

/**
 * Interface for RequestDataBuilders.
 * Intended for extension to create RequestData objects using data from specific models.
 *
 * Interface RequestDataBuilderInterface
 * @package Message\Cog\HTTP\REST
 */
interface RequestDataBuilderInterface
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

	/**
	 * Get fully populated instance of RequestData
	 *
	 * @return RequestData
	 */
	public function getRequestData();
}