<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Result;

class DBResultAdapter extends ArrayAdapter {

	public function setResults(Result $results)
	{
		$this->setArray($results);
	}

}