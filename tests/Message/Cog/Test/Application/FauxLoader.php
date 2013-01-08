<?php

namespace Message\Cog\Test\Application;

class FauxLoader
{
	protected $_baseDir;

	public function __construct($baseDir)
	{
		$this->_baseDir = $baseDir;
	}

	public function getBaseDir()
	{
		return $this->_baseDir;
	}
}