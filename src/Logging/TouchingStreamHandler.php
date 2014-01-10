<?php

namespace Message\Cog\Logging;

use Monolog\Handler\StreamHandler as BaseStreamHandler;

/**
 * Stream handler that silently creates the target file & any directories it is
 * in if they do not exist.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TouchingStreamHandler extends BaseStreamHandler
{
	/**
	 * {@inheritDoc}
	 *
	 * If the file does not exist, it is created with 0777 permissions.
	 */
	public function write(array $record)
	{
		if (null === $this->stream) {
			// From original monolog stream handler
			if (!$this->url) {
				throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
			}

			// Make the directory, if it doesn't exist
			$dir = dirname($this->url);
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}

			// Make the file, if it doesn't exist
			if (!file_exists($this->url)) {
				if (touch($this->url)) {
					chmod($this->url, 0777);
				}
			}
		}

		return parent::write($record);
	}
}