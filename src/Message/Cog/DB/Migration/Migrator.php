<?php

namespace Message\Cog\DB\Migration;

use Exception;

class Migrator {

	protected $_query;
	protected $_file;
	protected $_loader;
	protected $_creator;
	protected $_editor;
	protected $_collection;

	public function __construct($query, $file, $loader, $creator, $deletor, $collection)
	{
		$this->_query      = $query;
		$this->_file       = $file;
		$this->_loader     = $loader;
		$this->_creator    = $create;
		$this->_deletor    = $deletor;
		$this->_collection = $collection;
	}

	/**
	 * Run the outstanding migrations at a given path.
	 *
	 * @param  string $path
	 * @return void
	 */
	public function run($path)
	{
		// Find the migrations in the path that have not yet been run
		$inPath = $this->_loader->getFromPath($path);
		$run = $this->_loader->getAll();
		$migrations = array_diff($inPath, $run);

		// Add the new migrations to the collection
		foreach ($migrations as $migration) {
			$this->_collection->add($migration);
		}

		// Run the collection
		$this->_runCollection();
	}

	/**
	 * Run 'up' a migration.
	 * 
	 * @param  Migration $migration
	 * @param  int       $batch
	 * @return void
	 */
	public function runUp(Migration $migration, $batch)
	{
		$migration->up();

		$this->_creator->log($migration, $batch);
	}

	/**
	 * Run 'down' the last migration batch.
	 * 
	 * @return void
	 */
	public function rollback()
	{
		$migrations = $this->_loader->getLastBatch();

		foreach ($migrations as $migration) {
			$this->runDown($migration);
		}
	}

	/**
	 * Run 'up' a collection.
	 * 
	 * @return void
	 */
	protected function _runCollection()
	{
		if (count($this->_collection) == 0) {
			throw new Exception("Could not run migrations, migrator collection empty.");
		}

		$batch = $this->_getNextBatchNumber();

		foreach ($this->_collection as $basename => $file)
		{
			$migration = $this->_loader->resolve($file);
			try {
				$this->runUp($migration, $batch);
				$this->_collection->remove($basename);
			}
			catch (Exception $e) {
				// throw an exception saying the migration did not complete
			}
		}
	}

	/**
	 * Run 'down' a migration.
	 * 
	 * @param  Migration $migration
	 * @return void
	 */
	protected function _runDown(Migration $migration)
	{
		$migration->down();

		$this->_deletor->delete($migration);
	}

	/**
	 * Get the next batch number.
	 * 
	 * @return int
	 */
	protected function _getNextBatchNumber()
	{
		$batch = $this->_loader->getLastBatchNumber() + 1;

		return $batch;
	}

}