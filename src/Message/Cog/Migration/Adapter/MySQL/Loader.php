<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\LoaderInterface;
use Message\Cog\Filesystem\File;

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
				path
			FROM
				migration
			WHERE
				adapter = "mysql"
		');

		return $this->_getMigrations($results);
	}

	public function getFromPath($path)
	{
		$files = $this->_filesystem->files()->in($path);

		$migrations = array();

		foreach ($files as $file) {
			if ($file->isFile()) {
				$migrations[] = $this->resolve($file);
			}
		}

		return $migrations;
	}

	public function getLastBatch()
	{
		$results = $this->_connector->run('
			SELECT
				path
			FROM
				migration
			WHERE
				batch = ?i AND
				adapter = "mysql"
			ORDER BY
				run_at DESC
		', array(
			$this->getLastBatchNumber()
		));

		return $this->_getMigrations($results);
	}

	public function getLastBatchNumber()
	{
		$results = $this->_connector->run('
			SELECT
				batch
			FROM
				migration
			WHERE
				adapter = "mysql"
			ORDER BY
				batch DESC
			LIMIT 1
		');

		return $results[0]->batch;
	}

	public function resolve(File $file)
	{
		// Load the migration class
		include_once $file->getRealpath();

		// Get the class name
		$classname = str_replace('.php', '', $file->getBasename());

		return new $classname($file, $this->_connector);
	}

	public function install()
	{
		$this->_connector->run('
			CREATE TABLE IF NOT EXISTS
				migration (
					adapter VARCHAR (255),
					path TEXT,
					batch INT (11),
					run_at INT (11),
					PRIMARY KEY (migration_id)
				)
		');
	}

	protected function _getMigrations($results)
	{
		$migrations = array();

		foreach ($results as $row) {
			$file = new File($row->path);
			$migrations[] = $this->resolve($file);
		}

		return $migrations;
	}

}