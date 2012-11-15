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
	 * @return string             The path to the module directory
	 */
	public function getPath($moduleName)
	{
		static $paths = array();

		// If we haven't been asked for this module's directory before
		if (!isset($paths[$moduleName])) {
			$namespaceToFind = $moduleName;
			// Initiate infinite loop
			while (1) {
				if (isset($this->_namespaces[$namespaceToFind])) {
					// Get first path in the list for this namespace
					$directory = array_shift($this->_namespaces[$namespaceToFind]);
					// Ensure directory ends with directory separator
					if (substr($directory, -1) !== DIRECTORY_SEPARATOR) {
						$directory .= DIRECTORY_SEPARATOR;
					}
					// Add PSR-0 namespace
					$paths[$moduleName] = $directory . str_replace('\\', DIRECTORY_SEPARATOR, $moduleName) . DIRECTORY_SEPARATOR;

					// Quit the loop
					break;
				}
				else {
					// Take a namespace off the end and try again
					$namespaceToFind = substr($namespaceToFind, 0, strrpos($namespaceToFind, '\\'));
				}

			}
		}

		return $paths[$moduleName];
	}
}