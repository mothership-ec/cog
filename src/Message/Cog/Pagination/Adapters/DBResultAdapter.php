<?php

namespace Message\Cog\Pagination\Adapters;

use Pagerfanta\Adapter\AdapterInterface;

class DBResultAdapter implements AdapterInterface {

	protected $results;

	public function __construct()
	{
		//
	}

	public function setResults($results)
	{
		$this->results = $results;
	}

	public function getNbResults()
	{
		return count($this->results);
	}

	public function getSlice($offset, $length)
	{
		return array_slice($this->results, $offset, $length);
	}

}