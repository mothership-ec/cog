<?php

namespace Message\Cog\Pagination\Adapters;

use Pagerfanta\Adapter\ArrayAdapter;

class DBResultAdapter extends ArrayAdapter {

	public function setResults($results)
	{
		$this->array = $results;
	}

}