<?php

namespace Message\Cog\Filesystem;

use Symfony\Component\HttpFoundation\File\File as BaseFile;

/**
* An extension of SplFileInfo that enables us to add our own customisations.
*
* @author  James Moss <james@message.co.uk>
*/
class File extends \SplFileInfo
{
	const PUBLIC_DIR = 'public/';
	const COG_PREFIX = 'cog';

	protected $_reference;

	public function __construct($fileName)
	{
		$prefix = self::COG_PREFIX . '://';
		if(substr($fileName, 0, strlen($prefix)) === $prefix) {
			$this->_reference = $fileName;
		}

		parent::__construct($fileName);
	}

	/**
	 * Calculates the md5 checksum of a file.
	 *
	 * @return string 	the file's md5 checksum.
	 */
	public function getChecksum()
	{
		return md5_file($this->getRealPath());
	}

	/**
	 * Gets the publically accessible URL to a file.
	 *
	 * @todo  Don't hardcode the cog:// handler at the top of this class
	 * @todo  Be able to handle URLs that might be on a different hostname
	 *
	 * @return string The public path to the file.
	 */
	public function getPublicUrl()
	{
		if (!$this->isPublic()) {
			throw new \Exception(sprintf('`%s` is not publically accessible', $this->_reference));
		}

		$path = self::COG_PREFIX . '://' . self::PUBLIC_DIR;

		return '/' . substr($this->_reference, strlen($path));
	}

	/**
	 * Check if a file is publically accessible
	 *
	 * @return boolean Returns true if the file can be reached publically, false if not.
	 */
	public function isPublic()
	{
		$path = self::COG_PREFIX . '://' . self::PUBLIC_DIR;

		// Ensure our URL starts with PUBLIC_PATH
		return !strncmp($this->_reference, $path, strlen($path));
	}

	/**
	 * Returns the true filesystem path for a file.
	 *
	 * @return string The path to the file on the filesystem.
	 */
	public function getRealPath()
	{
		if($this->_reference) {
			$handler = StreamWrapperManager::getHandler(self::COG_PREFIX);

			return $handler->getLocalPath($this->_reference, self::COG_PREFIX);
		}

		return parent::getRealPath();
	}
}