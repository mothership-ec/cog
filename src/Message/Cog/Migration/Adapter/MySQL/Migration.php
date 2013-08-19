<?php

namespace Message\Cog\Migration\Adapter\MySQL;

use Message\Cog\Migration\Adapter\MigrationInterface;

abstract class Migration implements MigrationInterface {

	protected $query;

	public function __construct($query)
	{
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

}