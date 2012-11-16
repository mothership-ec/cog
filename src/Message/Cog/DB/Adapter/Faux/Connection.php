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

	protected $_data = array();
	protected $_sequenceData = array();
	protected $_sequencePointer = 0;

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
		if($this->_sequenceData) {
			$data = current($this->_sequenceData);
			next($this->_sequenceData);
		} else {
			$data = $this->_data;
		}

		return new Result($data, $this);
	}

	public function escape($text)
	{
		return $text;
	}

	public function getLastError()
	{
		return $this->_params['lastError'];
	}
}