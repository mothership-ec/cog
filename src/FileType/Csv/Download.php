<?php

namespace Message\Cog\FileType\Csv;

use Message\Cog\FileType\DownloadInterface;
use Message\Cog\HTTP\StreamedResponse;

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
		$table = $this->_table;
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
	 * @param $filename
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