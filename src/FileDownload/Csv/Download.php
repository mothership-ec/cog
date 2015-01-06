<?php

namespace Message\Cog\FileDownload\Csv;

use Message\Cog\FileDownload\AbstractDownload;
use Message\Cog\HTTP\StreamedResponse;

/**
 * Class for creating and force downloading a CSV file
 *
 * Class Download
 * @package Message\Cog\FileDownload\Csv
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Download extends AbstractDownload
{
	const EXT = 'csv';

	/**
	 * @var Table
	 */
	protected $_table;

	/**
	 * @var string
	 */
	protected $_type = 'text/csv';

	public function __construct(Table $table)
	{
		$this->_table = $table;
	}

	/**
	 * {@inheritDoc}
	 */
	public function download($filename = null)
	{
		if (null !== $filename) {
			$this->setFilename($filename);
		}

		if (null === $this->_response) {
			$this->_setResponse();
		}

		return $this->_response;
	}

	public function getExt()
	{
		return 'csv';
	}

	public function getClosure()
	{
		$table = $this->_table;

		return function() use ($table) {
			$handle = fopen('php://output', 'w');
			foreach ($table as $row) {
				fputcsv($handle, $row->getColumns());
			}
			fclose($handle);
		};
	}


}