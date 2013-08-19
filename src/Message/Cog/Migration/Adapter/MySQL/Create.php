<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\CreateInterface;

class Create implements CreateInterface {

	protected $_query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function log($migration, $batch)
	{
		
	}

}