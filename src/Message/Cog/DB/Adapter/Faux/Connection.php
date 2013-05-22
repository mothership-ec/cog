<?php

namespace Message\Cog\DB\Adapter\Faux;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Connection implements ConnectionInterface
{
	protected $_params = array(
		'result'		=> false,
		'affectedRows'  => 0,
		'insertId'		=> 0,
		'lastError'		=> '',
	);

	protected $_data            = array();
	protected $_sequenceData    = array();
	protected $_sequencePointer = 0;
	protected $_patternData     = array();

	public function __construct(array $params = array())
	{
		$this->_params = array_merge($this->_params, $params);
	}

	public function setResult($data)
	{
		$this->_data = $data;
	}

	public function setSequence($data)
	{
		reset($data);
		$this->_sequenceData = $data;
	}

	public function query($sql)
	{
		$data = false;

		if($this->_patternData) {
			foreach($this->_patternData as $pattern => $value) {
				if(preg_match($pattern, $sql)) {
					$data = $value;
					break;
				}
			}
		} else if($this->_sequenceData) {
			$data = current($this->_sequenceData);
			next($this->_sequenceData);
		} else {
			$data = $this->_data;
		}

		return new Result($data, $this);
	}

	public function setPattern($pattern, $data)
	{
		$this->_patternData[$pattern] = $data;
	}

	public function escape($text)
	{
		return $text;
	}

	public function getLastError()
	{
		return $this->_params['lastError'];
	}

	public function getInsertId()
	{
		return $this->_params['insertId'];
	}

	public function getAffectedRows()
	{
		return $this->_params['affectedRows'];
	}
}