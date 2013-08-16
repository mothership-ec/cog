<?php

namespace Message\Cog\DB\Migration;

class Delete {

	protected $_query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function delete($migration)
	{
		
	}

}