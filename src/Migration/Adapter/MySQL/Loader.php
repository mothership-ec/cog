<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\LoaderInterface;
use Message\Cog\Filesystem\File;
use Message\Cog\DB\Query;
use Message\Cog\Filesystem;
use Message\Cog\Module\ReferenceParserInterface;


class Loader implements LoaderInterface
{
	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	/**
	 * @var \Message\Cog\Filesystem\Finder
	 */
	protected $_finder;

	/**
	 * @var \Message\Cog\Filesystem\Filesystem
	 */
	protected $_filesystem;

	/**
	 * @var \Message\Cog\Module\ReferenceParserInterface
	 */
	protected $_referenceParser;

	public function __construct(
		Query $query,
		Filesystem\Finder $finder,
		Filesystem\Filesystem $filesystem,
		ReferenceParserInterface $referenceParser
	)
	{
		$this->_query           = $query;
		$this->_finder          = $finder;
		$this->_filesystem      = $filesystem;
		$this->_referenceParser = $referenceParser;
	}

	public function getAll()
	{
		$results = $this->_query->run('
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

		if (!$this->_filesystem->exists($path)) {
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
		$results = $this->_query->run('
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
		$results = $this->_query->run('
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

		if (!$results or count($results) == 0) {
			return 0;
		}

		return $results[0]->batch;
	}

	public function resolve(File $file, $reference)
	{
		// Load the migration class
		$path = $file->getPath();
		if (!file_exists($path)) {
			return false;
		}

		include_once $path;

		// Get the class name
		$classname = str_replace('.php', '', $file->getBasename());

		return new $classname($reference, $file, $this->_query);
	}

	public function install()
	{
		$this->_query->run('
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
			$migration   = $this->resolve($file, $row->path);

			if (false !== $migration) {
				$migrations[] = $this->resolve($file, $row->path);
			}
		}

		return $migrations;
	}

}