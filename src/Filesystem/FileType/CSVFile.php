<?php

namespace Message\Cog\Filesystem\FileType;

use Exception;
use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\Exception\InvalidFileException;

/**
 * Read a file as a csv.
 *
 * Usage
 *     $file = new CSVFile('/path/to/file.csv');
 *     $file->getFirstLineAsColumns(array('optional', 'array', 'of', 'expected', 'columns'));
 *     foreach ($file as $line) {
 *         echo $line['column_name'];
 *     }
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class CSVFile extends \SplFileObject {

	protected $_columns;
	protected $_firstLineIsColumns;

	public function __construct($fileName)
	{
		parent::__construct($fileName);

		$this->setFlags(self::DROP_NEW_LINE + self::READ_AHEAD + self::SKIP_EMPTY + self::READ_CSV);
	}

	/**
	 * Gets the first line as columns with an optional expected set of columns.
	 *
	 * @param  array $expected
	 * @return array
	 */
	public function getFirstLineAsColumns($expected = null)
	{
		$this->_firstLineIsColumns = true;
		$this->rewind();

		if (null !== $expected and $this->_columns != $expected) {
			throw new InvalidFileException("Columns did not match those expected on the first line");
		}

		return $this->getColumns();
	}

	public function rewind()
	{
		parent::rewind();

		// If we are getting the first line as columns, take them then skip to
		// the next line.
		if ($this->_firstLineIsColumns) {
			$this->setColumns(parent::current());
			parent::next();
		}
	}

	public function current()
	{
		if ($this->_columns) {
			if (false === $combined = @array_combine($this->_columns, parent::current())) {
				throw new Exception(sprintf("Column count did not match expected on line %d", parent::key()));
			}

			return $combined;
		}

		return parent::current();
	}

	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}

	public function getColumns()
	{
		return $this->_columns;
	}

}