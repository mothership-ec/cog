<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Result;

class DBResultAdapter extends ArrayAdapter {

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