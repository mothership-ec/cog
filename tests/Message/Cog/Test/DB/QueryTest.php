<?php

namespace Message\Cog\Test\DB;


class QueryTest extends \PHPUnit_Framework_TestCase
{
	
	public function testParamsAreEscaped()
	{
		$this->markTestIncomplete(
			'The query object needs more work so that we can test parameter parsing.'
		);

		$query = $this->getQuery();

		$query->run("
			SELECT
					*
				FROM
					page_content 
				WHERE
					page_id     = ?i
				AND language_id = ?
				AND country_id  = ?sn
				AND user_id = :user_id?in
				AND staff_name = :staff_name
				ORDER BY
					group, sequence, field_name, data_name
		", array(
			1337, 'UK', 'GB', 987, 'bob'
		));
	}

	public function getConnection()
	{
		return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
			'host'		=> '192.168.201.99',
			'user'		=> 'joe',
			'password' 	=> 'cheese',
			'db'		=> 'mothership_cms',
			'charset'	=> 'utf8',
		));
	}

	public function getQuery()
	{
		return new \Message\Cog\DB\Query($this->getConnection());
	}

}