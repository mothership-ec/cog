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
		$this->_query->run('
			INSERT INTO
				migration
			SET
				adapter = "mysql",
				path = ?s,
				batch = ?i,
				run_at = ?
		', array(
			$migration->getReference(),
			$batch,
			time()
		));
	}

}