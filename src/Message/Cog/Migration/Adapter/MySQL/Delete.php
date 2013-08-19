<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\DeleteInterface;

class Delete implements DeleteInterface {

	protected $_query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function delete($migration)
	{
		
	}

}