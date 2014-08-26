<?php

namespace Message\Cog\FileType\Csv;

class Row implements \IteratorAggregate, \Countable
{
	/**
	 * @var array
	 */
	private $_columns;

	public function __construct(array $columns)
	{
		$this->setColumns($columns);
	}

	public function getColumns()
	{
		return $this->_columns;
	}

	public function setColumns(array $columns)
	{
		$this->_parseColumns($columns);

		$this->_columns = $columns;
	}

	/**
	 * {@inheritDoc}
	 */
	public function count()
	{
		return count($this->_columns);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_columns);
	}

	private function _parseColumns(array &$columns)
	{
		array_walk($columns, function (&$column) {
			if (!$column instanceof Column) {
				$column = new Column($column);
			}
		});
	}
}