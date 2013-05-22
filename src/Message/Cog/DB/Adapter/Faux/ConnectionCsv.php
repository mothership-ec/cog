<?php

namespace Message\Cog\DB\Adapter\Faux;

/**
*
*/
class ConnectionCsv extends Connection
{
	public function setResult($path)
	{
		$this->_data = $this->_loadDataFromCsv($path);
	}

	public function setSequence($paths)
	{
		foreach($paths as $path) {
			$this->_sequenceData[] = $this->_loadDataFromCsv($path);
		}

		reset($this->_sequenceData);
	}

	public function setPattern($pattern, $path)
	{
		$this->_patternData[$pattern] = $this->_loadDataFromCsv($path);
	}

	protected function _loadDataFromCsv($path)
	{
		if (file_exists($path) === false) {
			throw \Exception(sprintf('`%s` does not exist.', $path));
		}

		if (($handle = fopen($path, 'r')) === false) {
			throw \Exception(sprintf('Cannot open `%s` for reading.', $path));
		}

		$keys = array();
		$data = array();
		while (($row = fgetcsv($handle, 4096)) !== FALSE) {
			if(!count($data)) { // first row must always be keys
				$keys = $row;
				continue;
			}

			$data[] = array_combine($keys, $row);
		}

		fclose($handle);

		return $data;
	}
}