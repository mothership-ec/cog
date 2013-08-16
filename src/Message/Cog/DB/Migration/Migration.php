<?php

namespace Message\Cog\DB\Migration;

abstract class Migration {

	protected $query;

	public function __construct($query)
	{
		$this->_query = $query;
	}

	public function run($sql)
	{
		$this->_query->run($sql);
	}

	public function up()
	{

	}

	public function down()
	{

	}

}