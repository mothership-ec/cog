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
			throw new \Exception(sprintf('`%s` is not publicly accessible', $this->_reference));
		}

		$path = $this->_getRealPublicPath();

		return '/' . substr($this->getRealPath(), strlen($path));
	}

	/**
	 * Check if a file is publically accessible
	 *
	 * @return boolean Returns true if the file can be reached publicly, false if not.
	 */
	public function isPublic()
	{
		$path = $this->_getRealPublicPath();

		// Ensure our URL starts with PUBLIC_PATH
		return !strncmp($this->getRealPath(), $path, strlen($path));
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

	/**
	 * Get the name of the file without the extension and period.
	 *
	 * @return string The filename without it's extension
	 */
	public function getFilenameWithoutExtension()
	{
		return $this->getBasename('.' . $this->getExtension());
	}

	/**
	 * Get the reference for the public path
	 *
	 * @return string
	 */
	private function _getPublicRef()
	{
		return self::COG_PREFIX . '://' . self::PUBLIC_DIR;
	}

	/**
	 * Get the real path to the public directory
	 *
	 * @todo This method sucks and I hate it, because it's essentially a hack, creating a new File instance just to use
	 * the method in File to get the real path. I'm sure there's a less stupid way to get it but at least it works!
	 *
	 * @return bool|string
	 */
	private function _getRealPublicPath()
	{
		$fakeFileHack = new File($this->_getPublicRef());

		return $fakeFileHack->getRealPath();
	}
}