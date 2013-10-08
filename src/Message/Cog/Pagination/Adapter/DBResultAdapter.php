<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Result;
use Pagerfanta\Adapter\AdapterInterface;

class DBResultAdapter implements AdapterInterface {

	protected $results;

	public function __construct()
	{
		//
	}

	public function setResults(Result $results)
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