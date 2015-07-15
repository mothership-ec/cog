<?php

namespace Message\Cog\Migration;

use Message\Cog\Migration\Adapter\MigrationInterface;
use Message\Cog\Filesystem\File;

use Exception;

class Migrator {

	protected $_loader;
	protected $_creator;
	protected $_editor;

	protected $_collection = array();
	protected $_notes = array();

	public function __construct(
		Adapter\LoaderInterface $loader,
		Adapter\CreateInterface $creator,
		Adapter\DeleteInterface $deletor)
	{
		$this->_loader     = $loader;
		$this->_creator    = $creator;
		$this->_deletor    = $deletor;
	}

	/**
	 * Run the outstanding migrations at a given path.
	 *
	 * @param  string $reference
	 * @return void
	 */
	public function run($reference)
	{
		$this->runFromReferences([$reference]);
	}

	public function runFromReferences(array $references)
	{
		$migrations = [];

		foreach ($references as $reference) {
			$migrationsForReference = $this->_loader->getFromReference($reference);

			if (count($migrationsForReference) === 0) {
				$this->_note("<comment>No migrations to run for `". $reference . "`</comment>");
			}

			$migrations = $migrations + $migrationsForReference;
		}

		ksort($migrations);

		$run = $this->_loader->getAll();

		// Diff the migrations in the path and those run to get new migrations
		foreach ($migrations as $key => $migration) {
			foreach ($run as $r) {
				if (get_class($migration) === get_class($r)) {
					$this->_note("<comment>Migration `" . $migration->getReference() . "` already run</comment>");
					unset($migrations[$key]);
				}
			}
		}

		// Add the new migrations to the collection
		foreach ($migrations as $migration) {
			$this->_collection[$migration->getReference()] = $migration;
		}

		// Run the collection
		$this->_runCollection();

		$this->_displayFailures();
	}

	/**
	 * Run 'up' a migration.
	 *
	 * @param  MigrationInterface $migration
	 * @param  int       $batch
	 * @return void
	 */
	public function runUp(Adapter\MigrationInterface $migration, $batch)
	{
		try {
			$migration->up();
			$this->_note('<comment>- Ran up ' . $migration->getReference() . '</comment>');
		}
		catch (Exception $e) {
			$this->_note('<error>- Failed to run up ' . $migration->getReference() . '</error>');
			$this->_note('<error>	with message: "' . $e->getMessage() . '"</error>');
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
			$this->_note('<comment>Nothing to rollback</comment>');

			return count($migrations);
		}

		foreach ($migrations as $migration) {
			$this->_runDown($migration);
		}

		$this->_displayFailures();

		return count($migrations);
	}

	/**
	 * Reset the database to the point before any migrations were run.
	 *
	 * @return void
	 */
	public function reset()
	{
		$prev = null;

		do {
			$count = $this->rollback();

			if ($prev === $count) {
				$this->_note('<error>Rollback count not reducing, breaking out of infinite loop');
				break;
			}

			$prev = $count;

		} while ($count > 0);
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

	public function clearNotes()
	{
		$this->_notes = array();
	}

	/**
	 * Run 'up' a collection.
	 *
	 * @return void
	 */
	protected function _runCollection()
	{
		if (count($this->_collection) == 0) {
			$this->_note("<comment>No migrations run</comment>");
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
	 * @param  MigrationInterface $migration
	 * @return void
	 */
	protected function _runDown(MigrationInterface $migration)
	{
		try {
			$migration->down();
			$this->_note('<comment>- Ran down ' . $migration->getReference() . '</comment>');
		}
		catch (Exception $e) {
			$this->_note('<error>- Failed to run down ' . $migration->getReference() . ' with message ' . $e->getMessage() . '</error>');
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

	private function _displayFailures()
	{
		$failures = $this->_loader->getFailures();

		if (!empty($failures) && !is_array($failures)) {
			throw new \InvalidArgumentException('Cannot display failed migrations, migration failures expected as an array, got ' . (gettype($failures) === 'object') ? get_class($failures) : gettype($failures));
		} elseif (empty($failures)) {
			return false;
		}

		foreach ($failures as $failure) {
			if (!is_string($failure)) {
				throw new \InvalidArgumentException('Cannot display failed migrations, expected a string, got ' . (gettype($failure) === 'object') ? get_class($failure) : gettype($failure));
			}

			$this->_note('<error>Could not load migration: `' . $failure . '`</error>');
		}
	}

}