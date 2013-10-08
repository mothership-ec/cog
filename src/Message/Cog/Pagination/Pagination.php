<?php

namespace Message\Cog\Pagination;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\AdapterInterface;

class Pagination {

	protected $_paginator;

	public function __construct(AdapterInterface $adapter)
	{
		$this->_paginator = new Pagerfanta($adapter);
	}

	public function setAdapter(AdapterInterface $adapter)
	{
		$this->_paginator = new Pagerfanta($adapter);
	}

	public function getCount()
	{
		return $this->_paginator->getAdapter()->getNbResults();
	}

	public function __call($method, $params)
	{
		if (method_exists($this->_paginator, $method)) {
			return call_user_func_array(array($this->_paginator, $method), $params);
		}

		$adapter = $this->_paginator->getAdapter();
		if (method_exists($adapter, $method)) {
			return call_user_func_array(array($adapter, $method), $params);
		}
	}

}