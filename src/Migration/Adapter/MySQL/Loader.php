<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\LoaderInterface;
use Message\Cog\Filesystem\File;

class Loader implements LoaderInterface {

	protected $_connector;
	protected $_finder;
	protected $_filesystem;
	protected $_referenceParser;

	public function __construct($connector, $finder, $filesystem, $referenceParser)
	{
		$this->_connector = $connector;
		$this->_finder = $finder;
		$this->_filesystem = $filesystem;
		$this->_referenceParser = $referenceParser;
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

	public function getFromReference($reference)
	{
		$reference .= '::';

		$this->_referenceParser->parse($reference);

		$path = $this->_referenceParser->getFullPath('resources/migrations');

		if (! $this->_filesystem->exists($path)) {
			return array();
		}

		$files = $this->_finder->files()->in($path);

		$migrations = array();

		foreach ($files as $file) {
			if ($file->isFile()) {
				$fileReference = 'cog://' . $reference . 'resources/migrations/' . $file->getBasename();
				$migrations[] = $this->resolve($file, $fileReference);
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
				run_at DESC,
				migration_id DESC
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

		if (! $results or count($results) == 0) {
			return 0;
		}

		return $results[0]->batch;
	}

	public function resolve(File $file, $reference)
	{
		$path = $file->getRealpath();
		$classname = str_replace('.php', '', $file->getBasename());

		if (!file_exists($path)) {
			return null;
		}

		// Load the migration class
		include_once $path;

		// Get the class name
		return new $classname($reference, $file, $this->_connector);
	}

	public function install()
	{
		$this->_connector->run('
			CREATE TABLE IF NOT EXISTS
				migration (
					migration_id INT (11) AUTO_INCREMENT,
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
			$migrations[] = $this->resolve($file, $row->path);
		}

		return $migrations;
	}

}