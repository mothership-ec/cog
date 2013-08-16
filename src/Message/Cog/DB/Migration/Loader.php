<?php

namespace Message\Cog\DB\Migration;

class Loader {

	protected $_query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function getLastBatch()
	{
		$results = $this->_query->run('
			SELECT
				filename
			FROM
				migrations
			WHERE
				batch = (SELECT batch FROM migrations ORDER BY batch DESC LIMIT 1)
			ORDER BY
				run_at DESC
		');

		return $this->_getMigrations($results);
	}

	public function resolve($file)
	{
		// Load the migration class
		include $file->getPath();

		// Get the class name
		$classname = str_replace('.php', '', $basename);

		return new $classname($this->_query);
	}

	public function getLastBatchNumber()
	{
		$results = $this->_query->run('
			SELECT
				batch
			FROM
				migrations
			ORDER BY
				batch DESC
			LIMIT 1
		');

		return $results[0]->batch;
	}

	protected function _getMigrations($results)
	{
		$migrations = array();

		foreach ($results as $row) {
			$file = new File($row->filename);
			$migrations = $this->resolve($file);
		}

		return $migrations;
	}

}