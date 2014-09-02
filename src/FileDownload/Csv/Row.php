<?php

namespace Message\Cog\FileDownload\Csv;

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

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * @param array $columns
	 */
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

	public function getSimpleColumns()
	{
		$columns = $this->_columns;

		array_walk($columns, function(&$column) {
			$column = $column->getValue();
		});

		return $columns;
	}

	/**
	 * Cast all columns as a Column object
	 *
	 * @param array $columns
	 */
	private function _parseColumns(array &$columns)
	{
		array_walk($columns, function (&$column) {
			if (!$column instanceof Column) {
				$column = new Column($column);
			}
		});
	}
}