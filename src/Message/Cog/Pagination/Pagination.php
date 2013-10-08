<?php

namespace Message\Cog\Pagination;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\AdapterInterface;

class Pagination {

	public function __construct(AdapterInterface $adapter) {
		$this->_paginator = new Pagerfanta($adapter);
	}

	public function __call($method, $params)
	{
		if (method_exists($this->_paginator, $method)) {
			return call_user_func_array(array($this->_paginator, $method), $params);
		}
	}

}