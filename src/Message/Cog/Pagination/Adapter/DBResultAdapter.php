<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Result;

class DBResultAdapter extends ArrayAdapter {

	/**
	 * Adds typehint to constructor.
	 *
	 * @param Result $array
	 */
	public function __construct(Result $array = null)
	{
		parent::__construct($result);
	}

	/**
	 * Provide a more semantic way of setting the results for the adapter.
	 *
	 * @param Result $results
	 */
	public function setResults(Result $results)
	{
		$this->setArray($results);
	}

}