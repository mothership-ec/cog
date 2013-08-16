<?php

namespace Message\Cog\DB\Migration;

class Create {

	protected $_query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function log($migration, $batch)
	{
		
	}

}