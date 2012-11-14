<?php

namespace Message\Cog\DB\Connection;

/**
*
*/
class Dummy implements ConnectionInterface
{
	const CSV_KEY = 'csv';

	protected $_params = array(
		'result'		=> false,
		'affectedRows'  => 0,
		'insertId'		=> 0,
		'lastError'		=> '',
	);


	public function __construct(array $params)
	{
		$this->_params = array_merge($this->_params, $params);
	}

	public function query($sql)
	{
		if($this->_params['csv']) {
			return $this->_loadFromCsv($this->_params['csv']);
		}
		
		return $this->_params['result'];
	}

	public function escape($text)
	{
		return $text;
	}

	public function getLastError()
	{
		return $this->_params['lastError'];
	}

	public function getAffectedRows()
	{
		return $this->_params['affectedRows'];
	}

	public function getLastInsertId()
	{
		return $this->_params['lastError'];
	}

	protected function _loadFromCsv($path)
	{
		# code...
	}
}