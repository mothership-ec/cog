<?php

namespace Message\Cog\Pagination;

use Message\Cog\Pagination\Adapter\AdapterInterface;

class Pagination {

	protected $_adapter;
	protected $_currentPage;
	protected $_maxPerPage;

	public function __construct(AdapterInterface $adapter)
	{
		$this->setAdapter($adapter);
	}

	public function setAdapter(AdapterInterface $adapter)
	{
		$this->_adapter = $adapter;
	}

	public function getAdapter()
	{
		return $this->_adapter;
	}

	public function setMaxPerPage($max)
	{
		$this->_maxPerPage = (int) $max;
	}

	public function getMaxPerPage()
	{
		return $this->_maxPerPage;
	}

	public function setCurrentPage($page)
	{
		$this->_currentPage = max(1, (int) $page);
	}

	public function getCurrentPage()
	{
		return $this->_currentPage;
	}

	public function getCurrentPageResults()
	{
		return $this->_adapter->getSlice(($this->getCurrentPage() - 1), $this->getMaxPerPage());
	}

	public function getCountPages()
	{
		return (int) floor($this->_adapter->getCount() / $this->getMaxPerPage());
	}

	public function hasNextPage()
	{
		return $this->getCurrentPage() < $this->getCountPages();
	}

	public function getNextPage()
	{
		return $this->getCurrentPage() + 1;
	}

	public function hasPreviousPage()
	{
		return $this->getCurrentPage() > 1;
	}

	public function getPreviousPage()
	{
		return $this->getCurrentPage() - 1;
	}

	public function __call($method, $parameters)
	{
		if (method_exists($this->_adapter, $method)) {
			return call_user_func_array(array($this->_adapter, $method), $parameters);
		}
	}

}