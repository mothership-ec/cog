<?php

namespace Message\Cog\Migration;

use Exception;

class Migrator {

	protected $_loader;
	protected $_creator;
	protected $_editor;

	protected $_collection = array();
	protected $_notes = array();

	public function __construct($loader, $creator, $deletor)
	{
		$this->_loader     = $loader;
		$this->_creator    = $creator;
		$this->_deletor    = $deletor;
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
			$this->_collection[$migration->getFile()->getRealpath()] = $migration;
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
		try {
			$migration->up();
			$this->_note('<comment>- Ran up ' . $migration->getFile()->getBasename() . '</comment>');
		}
		catch (Exception $e) {
			$this->_note('<error>- Failed to run up ' . $migration->getFile()->getBasename() . '</error>');
			return;
		}

		$this->_creator->log($migration, $batch);
	}

	/**
	 * Run 'down' the last migration batch.
	 * 
	 * @return int
	 */
	public function rollback()
	{
		$migrations = $this->_loader->getLastBatch();

		if (count($migrations) == 0) {
			$this->_note('<info>Nothing to rollback</info>');

			return count($migrations);
		}

		foreach ($migrations as $migration) {
			$this->_runDown($migration);
		}

		return count($migrations);
	}

	/**
	 * Reset the database to the point before any migrations were run.
	 * 
	 * @return void
	 */
	public function reset()
	{
		while (true) {
			$count = $this->rollback();

			if ($count == 0) {
				break;
			}
		}
	}

	/**
	 * Refresh the database by resetting then running all migrations.
	 * 
	 * @return void
	 */
	public function refresh()
	{
		$migrations = $this->_loader->getAll();

		$this->reset();

		foreach ($migrations as $name => $migration) {
			$this->_collection[$name] = $migration;
		}

		$this->_runCollection();
	}

	/**
	 * Get the migration notes.
	 * 
	 * @return array
	 */
	public function getNotes()
	{
		return $this->_notes;
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

		foreach ($this->_collection as $name => $migration) {
			try {
				$this->runUp($migration, $batch);
				unset($this->_collection[$name]);
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
		try {
			$migration->down();
			$this->_note('<comment>- Ran down ' . $migration->getFile()->getBasename() . '</comment>');
		}
		catch (Exception $e) {
			$this->_note('<error>- Failed to run down ' . $migration->getFile()->getBasename() . '</error>');
			return;
		}

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

	/**
	 * Add a note to the operation.
	 * 
	 * @param  string $note
	 * @return void
	 */
	protected function _note($note)
	{
		$this->_notes[] = $note;
	}

}