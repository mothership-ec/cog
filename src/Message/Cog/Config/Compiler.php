<?php

namespace Message\Cog\Config;

class Compiler
{
	protected $_dataSets = array();

	public function add(object $data)
	{
		return $this->_dataSets[] = $data;
	}

	public function clear()
	{
		$this->_datasets = array();
	}

	public function compile()
	{
		// return a Group object with the compiled data
	}
}