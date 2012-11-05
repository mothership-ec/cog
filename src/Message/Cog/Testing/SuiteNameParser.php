<?php

namespace Message\Cog\Testing;

/**
 * Parses unit test suite names.
 *
 * Unit suite names are formatted as follows:
 *  * vendor
 *  * vendor/symfony
 *  * vendor/symfony/eventdispatcher
 *  * [appName]
 *  * [appName]/[moduleName]
 */
class SuiteNameParser
{
	protected $_suiteName;
	protected $_path;

	public function parse($suiteName)
	{
		$this->_suiteName = strtolower((string) $suiteName);
		$parts            = explode('/', $this->_suiteName);

		// Set the base path
		if (in_array($parts[0], array('vendor', 'bespoke'))) {
			$this->_path = $parts[0] . '/';
		}
		else {
			$this->_path = 'system/library/';
		}

		// If the suite is more granular than just the section, complete the path
		if (count($parts) > 1) {
			foreach ($parts as $part) {
				$this->_path .= $part . '/';
			}
		}

		return $this;
	}

	/**
	 * Checks if the parsed suite path exists in the filesystem.
	 *
	 * @return [type] [description]
	 */
	public function exists()
	{
		$this->_checkEmpty();

		return file_exists(SYSTEM_PATH . $this->_path);
	}

	public function getPath()
	{
		$this->_checkEmpty();

		return $this->_path;
	}

	protected function _checkEmpty()
	{
		if (is_null($this->_suiteName)) {
			// TODO: change this to a more appropriate SPL exception. the one about
			// calling something on an empty container?
			throw new \Exception('No suite name has been parsed yet.');
		}
	}
}