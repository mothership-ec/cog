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
	protected $_public;

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

	public function getReference()
	{
		return $this->_reference;
	}

	/**
	 * Gets the publically accessible URL to a file.
	 *
	 * @todo  Don't hardcode the cog:// handler at the top of this class
	 * @todo  Be able to handle URLs that might be on a different hostname
	 *
	 * @throws \LogicException
	 *
	 * @return string The public path to the file.
	 */
	public function getPublicUrl()
	{
		if (!$this->isPublic()) {
			$path = ($this->_reference) ?: $this->getRealPath();

			throw new \LogicException(sprintf('`%s` is not publicly accessible', $path));
		}

		$path = $this->_getRealPublicPath();

		return '/' . substr($this->getRealPath(), strlen($path));
	}

	/**
	 * Check if a file is publicly accessible
	 *
	 * @return boolean Returns true if the file can be reached publicly, false if not.
	 */
	public function isPublic()
	{
		if (null === $this->_public) {
			$this->_setPublic();
		}

		return $this->_public;
	}

	/**
	 * Returns the true filesystem path for a file.
	 *
	 * @return string The path to the file on the filesystem.
	 */
	public function getRealPath()
	{
		if($this->_reference) {
			return $this->_getPathFromRef($this->_reference);
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
	 * @return bool|string
	 */
	private function _getRealPublicPath()
	{
		$v = $this->_getPathFromRef($this->_getPublicRef());
		return $this->_getPathFromRef($this->_getPublicRef());
	}

	/**
	 * Check the file's real path  and set $this->_public to true if it contains the public path
	 */
	private function _setPublic()
	{
		$path = $this->_getRealPublicPath();
		$rp = $this->getRealPath();

		$this->_public = !strncmp($this->getRealPath(), $path, strlen($path));
	}

	/**
	 * Convert a cog:// reference to a real path
	 *
	 * @param $ref
	 * @return mixed
	 */
	private function _getPathFromRef($ref)
	{
		$handler = StreamWrapperManager::getHandler(self::COG_PREFIX);
		$v = $handler->getLocalPath($ref, self::COG_PREFIX);
		return $handler->getLocalPath($ref, self::COG_PREFIX);
	}
}