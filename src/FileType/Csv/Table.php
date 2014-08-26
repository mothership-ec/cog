<?php


namespace Message\Cog\FileType\Csv;

class Table implements \IteratorAggregate, \Countable
{
	private $_rows;

	public function __construct(array $rows)
	{
		$this->setRows($rows);
	}

	public function getRows()
	{
		return $this->_rows;
	}

	public function setRows(array $rows)
	{
		foreach ($rows as $row) {
			if (!$row instanceof Row) {
				throw new \InvalidArgumentException('Expecting be an instance of Row, ' . gettype($row) . ' given');
			}
		}

		$this->_rows = $rows;
	}

	public function count()
	{
		return count($this->_rows);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_rows);
	}
}