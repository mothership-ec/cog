<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\MigrationInterface;

abstract class Migration implements MigrationInterface
{

	protected $_query;
	protected $_file;

	public function __construct($reference, $file, $query)
	{
		$this->_reference = $reference;
		$this->_file = $file;
		$this->_query = $query;
	}

	public function run($command)
	{
		$this->_query->run($command);
	}

	public function getFile()
	{
		return $this->_file;
	}

	public function getReference()
	{
		return $this->_reference;
	}

}