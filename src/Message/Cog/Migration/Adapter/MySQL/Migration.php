<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\MigrationInterface;

abstract class Migration implements MigrationInterface {

	protected $_query;
	protected $_file;

	public function __construct($file, $query)
	{
		$this->_file = $file;
		$this->_query = $query;
	}

	public function run($command)
	{
		$this->_query->run($command);
	}

	public function up()
	{

	}

	public function down()
	{

	}

	public function getFile()
	{
		return $this->_file;
	}

}