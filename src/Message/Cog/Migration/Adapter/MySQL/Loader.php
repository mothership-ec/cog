<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\LoaderInterface;

class Loader implements LoaderInterface {

	protected $_connector;
	protected $_filesystem;

	public function __construct($connector, $filesystem)
	{
		$this->_connector = $connector;
		$this->_filesystem = $filesystem;
	}

	public function getAll()
	{
		$results = $this->_connector->run('
			SELECT
				filename
			FROM
				migrations
		');

		return $this->_getMigrations($results);
	}

	public function getFromPath($path)
	{
		//
	}

	public function getLastBatch()
	{
		$results = $this->_connector->run('
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

	public function getLastBatchNumber()
	{
		$results = $this->_connector->run('
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

	public function resolve($file)
	{
		// Load the migration class
		include $file->getPath();

		// Get the class name
		$classname = str_replace('.php', '', $basename);

		return new $classname($this->_connector);
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