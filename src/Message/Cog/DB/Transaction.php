<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Transaction implements QueryableInterface
{
	protected $_query;
	protected $_connection;
	protected $_queries = array();

	public function __construct(ConnectionInterface $connection)
	{
		$this->_connection = $connection;
		$this->_query = new Query($connection);
		$this->_query->fromTransaction = true;
	}

	public function add($query, $params = array())
	{
		$this->_queries[] = array($query, $params);

		return $this;
	}

	public function run($query, $params = array())
	{
		return $this->add($query, $params);
	}

	public function rollback()
	{
		return $this->_query->run($this->_connection->getTransactionRollback());
	}

	public function commit()
	{
		$this->_query->run($this->_connection->getTransactionStart());
		try {
			foreach($this->_queries as $query) {
				$this->_query->run($query[0], $query[1]);
			}
		} catch(Exception $e) {
			$this->rollback();
			throw $e;
		}

		return $this->_query->run($this->_connection->getTransactionEnd());
	}

	public function setIDVariable($name)
	{
		return $this->add("SET @".$name." = ".$this->_connection->getLastInsertIdFunc());
	}

	public function getIDVariable($name)
	{
		return $this->_query->run("SELECT @".$name)->value();
	}

	public function getID()
	{
		return $this->_query->run("SELECT ".$this->_connection->getLastInsertIdFunc())->value();
	}
}