<?php

namespace Message\Cog\FileDownload\Csv;

class Column
{
	private $_value = '';

	public function __construct($value = null)
	{
		if ($value) {
			$this->setValue($value);
		}
	}

	public function setValue($value)
	{
		$this->_value = (string) $value;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function __toString()
	{
		return (string) $this->getValue();
	}
}