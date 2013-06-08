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
	const PUBLIC_PATH = 'cog://public/';

	protected $_reference;

	public function __construct($fileName)
	{
		$this->_reference = $fileName;

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

		return '/'.substr($this->_reference, strlen(self::PUBLIC_PATH));
	}

	/**
	 * Check if a file is publically accessible
	 *
	 * @return boolean Returns true if the file can be reached publically, false if not.
	 */
	public function isPublic()
	{
		// Ensure our URL starts with PUBLIC_PATH
		return !strncmp($this->_reference, self::PUBLIC_PATH, strlen(self::PUBLIC_PATH));
	}

	/**
	 * Returns the true filesystem path for a file.
	 *
	 * @return string The path to the file on the filesystem.
	 */
	public function realpath()
	{
		return StreamWrapperManager::getHandler('cog')->getLocalPath($this->_reference, 'cog');
	}
}