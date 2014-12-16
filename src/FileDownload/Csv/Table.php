<?php


namespace Message\Cog\FileDownload\Csv;

class Table implements \IteratorAggregate, \Countable
{
	private $_rows;

	public function __construct(array $rows)
	{
		$this->setRows($rows);
	}

	/**
	 * @return array
	 */
	public function getRows()
	{
		return $this->_rows;
	}

	/**
	 * @param array $rows
	 *
	 * @throws \InvalidArgumentException throws exception if a row is not an instance of Row
	 */
	public function setRows(array $rows)
	{
		array_walk($rows, function(&$row) {
			if (is_array($row)) {
				$row = new Row($row);
			} elseif (!$row instanceof Row) {
				throw new \InvalidArgumentException('Expecting be an instance of Row, ' . gettype($row) . ' given');
			}
		});

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