<?php

namespace Message\Cog\Module;

/**
 * Handles locating module directories within the file system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Locator implements LocatorInterface
{
	protected $_namespaces;
	protected $_paths;

	/**
	 * Constructor.
	 *
	 * @param array $namespaces Array with namespace as key and directories as
	 *                          value from the auto loader.
	 */
	public function __construct(array $namespaces)
	{
		$this->_namespaces = $namespaces;
	}

	/**
	 * Gets the path to the module directory.
	 *
	 * @param  string $moduleName Module name (e.g. Message\Raven)
	 *
	 * @return string             The path to the module directory
	 *
	 * @throws \InvalidArgumentException If the module could not be loaded
	 */
	public function getPath($moduleName, $inLibrary = true)
	{
		$key = $moduleName . '-' . (int) $inLibrary;

		// If we haven't been asked for this module's directory before
		if (!isset($this->_paths[$key])) {
			$namespaceToFind = $moduleName;
			// Initiate infinite loop
			while (1) {
				if (isset($this->_namespaces[$namespaceToFind])) {
					// Get first path in the list for this namespace
					$directory = reset($this->_namespaces[$namespaceToFind]);
					// Ensure directory ends with directory separator
					$directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
					if ($inLibrary) {
						// Add PSR-0 namespace
						$this->_paths[$key] = $directory;
					}
					else {
						// Remove source directory
						if (false !== ($srcPos = strpos($directory, 'src/'))) {
							$directory = substr($directory, 0, $srcPos);
						}

						$this->_paths[$key] = $directory;
					}

					// Quit the loop
					break;
				}
				else {
					// Quit the loop & throw exception if we can't remove any more namespaces
					if (!strpos($namespaceToFind, '\\')) {
						throw new \InvalidArgumentException(sprintf('Module `%s` could not be located.', $moduleName));
					}
					// Take a namespace off the end and try again
					$namespaceToFind = substr($namespaceToFind, 0, strrpos($namespaceToFind, '\\'));
				}

			}
		}

		return $this->_paths[$key];
	}
}