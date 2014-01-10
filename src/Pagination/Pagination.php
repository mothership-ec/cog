<?php

namespace Message\Cog\Pagination;

use Message\Cog\Pagination\Adapter\AdapterInterface;

class Pagination
{

	protected $_adapter;
	protected $_currentPage;
	protected $_maxPerPage;

	/**
	 * Constructor.
	 *
	 * @param AdapterInterface $adapter
	 */
	public function __construct(AdapterInterface $adapter)
	{
		$this->setAdapter($adapter);
	}

	/**
	 * Set the adapter.
	 *
	 * @param  AdapterInterface $adapter
	 * @return Pagination
	 */
	public function setAdapter(AdapterInterface $adapter)
	{
		$this->_adapter = $adapter;

		return $this;
	}

	/**
	 * Get the adapter.
	 *
	 * @return Adapter\AdapterInterface
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}

	/**
	 * Set the max number of results listed per page.
	 *
	 * @param  int $max
	 * @return Pagination
	 */
	public function setMaxPerPage($max)
	{
		$this->_maxPerPage = (int) $max;

		return $this;
	}

	/**
	 * Get the max number of results listed per page.
	 *
	 * @return int
	 */
	public function getMaxPerPage()
	{
		return $this->_maxPerPage;
	}

	/**
	 * Set the current page.
	 *
	 * @param  int $page
	 * @return Pagination
	 */
	public function setCurrentPage($page)
	{
		$this->_currentPage = max(1, (int) $page);

		return $this;
	}

	/**
	 * Get the current page.
	 *
	 * @return int
	 */
	public function getCurrentPage()
	{
		return $this->_currentPage;
	}

	/**
	 * Get the slice of results for the current page.
	 *
	 * @return array
	 */
	public function getCurrentPageResults()
	{
		return $this->_adapter->getSlice(($this->getCurrentPage() - 1), $this->getMaxPerPage());
	}

	/**
	 * Get the count of pages.
	 *
	 * @return int
	 */
	public function getCountPages()
	{
		return (int) ceil($this->_adapter->getCount() / $this->getMaxPerPage());
	}

	/**
	 * Check if there is another page available after the current one.
	 *
	 * @return boolean
	 */
	public function hasNextPage()
	{
		return $this->getCurrentPage() < $this->getCountPages();
	}

	/**
	 * Get the next page number.
	 *
	 * @return int
	 */
	public function getNextPage()
	{
		return $this->getCurrentPage() + 1;
	}

	/**
	 * Check if there is a page available before the current one.
	 *
	 * @return boolean
	 */
	public function hasPreviousPage()
	{
		return $this->getCurrentPage() > 1;
	}

	/**
	 * Get the previous page number.
	 *
	 * @return int
	 */
	public function getPreviousPage()
	{
		return $this->getCurrentPage() - 1;
	}

	/**
	 * Pass through method calls to the adapter.
	 *
	 * @param  string $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (method_exists($this->_adapter, $method)) {
			return call_user_func_array(array($this->_adapter, $method), $parameters);
		}
	}

}