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

	/**
	 * @var array
	 */
	private $_failures = [];

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

		$files = $this->_getFinder()->files()->in($path);

		$migrations = array();

		foreach ($files as $file) {
			if ($file->isFile()) {
				$fileReference = 'cog://' . $reference . 'resources/migrations/' . $file->getBasename();
				$key = $this->_getKey($fileReference);
				$migrations[$key] = $this->resolve($file, $fileReference);
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

	public function getByModule($moduleName)
	{
		$moduleName = 'cog://@' . $moduleName . '%';

		$results = $this->_query->run('
			SELECT
				path
			FROM
				migration
			WHERE
				adapter = :adapter?s
			AND
				path LIKE :moduleName?s
		', [
			'adapter'    => 'mysql',
			'moduleName' => $moduleName,
		]);

		return $this->_getMigrations($results);
	}

	public function resolve(File $file, $reference)
	{
		$basename = $file->getBasename();
		$realPath = $file->getRealPath();

		if (!file_exists($realPath)) {
			return false;
		}

		include_once $realPath;

		// Get the class name
		$classname = str_replace('.php', '', $basename);

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

	public function getFailures()
	{
		$failures = $this->_failures;
		$this->_failures = [];

		return $failures;
	}

	protected function _getMigrations($results)
	{
		$migrations = array();

		foreach ($results as $row) {
			$file = new File($row->path);
			$migration   = $this->resolve($file, $row->path);

			$key = (false !== $migration) ? $this->_getKey($row->path) : $this->_getKey($file->getRealPath());

			if (false !== $migration) {
				$migrations[$key] = $this->resolve($file, $row->path);
			} else {
				$this->_failures[$key] = $file->getRealPath();
			}
		}

		return $migrations;
	}

	/**
	 * Use the timestamp in the filename to create a key, allowing the Migrator to order the migrations in chronological
	 * order
	 *
	 * @param $path
	 *
	 * @return string
	 */
	private function _getKey($path)
	{
		if (!is_string($path)) {
			throw new \InvalidArgumentException('Path must be a string, ' . gettype($path) . ' given');
		}

		$valid = preg_match('/_[0-9]+_[_A-Za-z0-9]+\.php/', $path, $matches);

		if ($valid) {
			$match = array_shift($matches);
			$valid = preg_match('/[0-9]+/', $match, $timestamp);

			if ($valid) {
				$timestamp = array_shift($timestamp);
				$timestamp = str_pad($timestamp, 20, '0', STR_PAD_LEFT);

				return $timestamp . $match;
			} else {
				throw new \LogicException('Could match filename in `' . $path . '` but not a timestamp');
			}
		}

		throw new \LogicException('Could not find valid timestamp in `' . $path . '`');
	}

	private function _getFinder()
	{
		return clone $this->_finder;
	}

}