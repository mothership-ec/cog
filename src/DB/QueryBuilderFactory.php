<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;
use Message\Cog\DB\QueryParser;

/**
 * QueryBuilder factory.
 *
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 */
class QueryBuilderFactory
{
	private $_connection;
	private $_parser;

	public function __construct(ConnectionInterface $connection, QueryParser $parser)
	{
		$this->_connection = $connection;
		$this->_parser = $parser;
	}

	public function getQueryBuilder()
	{
		return new QueryBuilder($this->_connection, $this->_parser);
	}
}