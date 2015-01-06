<?php

namespace Message\Cog\FileDownload;

use Message\Cog\HTTP\StreamedResponse;

abstract class AbstractDownload implements DownloadInterface
{
	/**
	 * @var string
	 */
	protected $_filename = 'download';

	/**
	 * @var string
	 */
	protected $_type = 'text/plain';

	/**
	 * @var StreamedResponse
	 */
	protected $_response;

	public function getExt()
	{
		return 'txt';
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFilename($filename)
	{
		$this->_filename = $this->_parseFilename($filename);
	}

	public function getFilename()
	{
		return $this->_filename;
	}

	/**
	 * Remove extension onto filename if it is not already there
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	protected function _parseFilename($filename)
	{
		$filename = (string) $filename;
		$filename = str_replace($this->getExt(), '', $filename);

		return $filename;
	}

	/**
	 * Instanciate the `StreamedResponse` object to create CSV file to download
	 */
	protected function _setResponse()
	{
		$fileName = $this->getFilename() . '.' . $this->getExt();

		$response = new StreamedResponse($this->getClosure());

		$response->headers->set('Content-Type', $this->_type);
		$response->headers->set('Content-Disposition','attachment; filename="' . $fileName . '"');

		$this->_response = $response;
	}
}