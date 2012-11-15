<?php

namespace Message\Cog\Module;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Document
{
	const FILE_NAME = 'info.md';

	protected $_info;
	protected $_readMe;

	static public function create($modulePath)
	{
		// Ensure path ends with directory separator
		if (substr($modulePath, -1) !== DIRECTORY_SEPARATOR) {
			$modulePath .= DIRECTORY_SEPARATOR;
		}

		// Add file name to end of path
		$filePath = $modulePath . self::FILE_NAME;

		if (!file_exists($filePath)) {
			throw new Exception(
				'Module document does not exist: `' . $filePath . '`',
				Exception::DOCUMENT_NOT_FOUND
			);
		}
		if (!is_readable($filePath)) {
			throw new Exception(
				'Module document is not readable: `' . $filePath . '`',
				Exception::DOCUMENT_NOT_READABLE
			);
		}
		return new self(file_get_contents($filePath));
	}

	public function __construct($contents)
	{
		$this->_parseYamlFrontMatter($contents);
	}

	public function getInfo()
	{
		return (object) $this->_info;
	}

	public function getReadMe()
	{
		return $this->_readMe;
	}

	protected function _parseYamlFrontMatter($contents)
	{
		try {
			$parts         = preg_split('/[\n]*[-]{3}[\n]/', $contents, 3);
			$this->_info   = Yaml::parse($parts[1]);
			$this->_readMe = $parts[2];
		}
		catch (ParseException $e) {
			throw new Exception(
				'Invalid module document: `' . $contents . '`',
				Exception::DOCUMENT_INVALID
			);
		}
	}
}