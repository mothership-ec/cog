<?php

namespace Message\Cog\FileDownload;

use Message\Cog\HTTP\StreamedResponse;

interface DownloadInterface
{
	/**
	 * Trigger download of the file.
	 *
	 * This method returns a response and therefore in order to trigger the download it needs to be returned by
	 * a controller
	 *
	 * @param string | null $filename   The name of the file to download. A default should be set in the class.
	 *
	 * @return StreamedResponse
	 */
	public function download($filename = null);

	/**
	 * Set the name of the file to be downloaded.
	 *
	 * @param $filename
	 */
	public function setFilename($filename);

	/**
	 * @return string
	 */
	public function getFilename();

	/**
	 * Get the extension of the file
	 *
	 * @return string
	 */
	public function getExt();

	/**
	 * @return \Closure
	 */
	public function getClosure();
}