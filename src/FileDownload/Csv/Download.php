<?php

namespace Message\Cog\FileDownload\Csv;

use Message\Cog\FileDownload\DownloadInterface;
use Message\Cog\HTTP\StreamedResponse;

/**
 * Class for creating and force downloading a CSV file
 *
 * Class Download
 * @package Message\Cog\FileDownload\Csv
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Download implements DownloadInterface
{
	const EXT = 'csv';

	/**
	 * @var Table
	 */
	protected $_table;

	/**
	 * @var StreamedResponse
	 */
	private $_response;

	/**
	 * @var string
	 */
	private $_filename = 'download';

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

	/**
	 * {@inheritDoc}
	 */
	public function setFilename($filename)
	{
		$this->_filename = $this->_parseFilename($filename);
	}

	/**
	 * Instanciate the `StreamedResponse` object to create CSV file to download
	 */
	private function _setResponse()
	{
		$table    = $this->_table;
		$fileName = $this->_filename . '.csv';

		$response = new StreamedResponse(function() use ($table) {
			$handle = fopen('php://output', 'w');
			foreach ($table as $row) {
				fputcsv($handle, $row->getColumns());
			}
			fclose($handle);
		});

		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Content-Disposition','attachment; filename="' . $fileName . '"');

		$this->_response = $response;
	}

	/**
	 * Add csv extension onto filename if it is not already there
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	private function _parseFilename($filename)
	{
		$filename = (string) $filename;

		$parts = explode($filename, '.');
		$last  = array_pop($parts);

		if ($last === self::EXT) {
			$parts[] = $last;
		}

		return implode($filename, '.');
	}
}