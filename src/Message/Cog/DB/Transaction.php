<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Transaction
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

	public function setID($name)
	{
		return $this->add("SET @".$name." = ".$this->_result->getLastInsertIdFunc());
	}

	public function getID()
	{
		return $this->add("SELECT ".$this->_result->getLastInsertIdFunc());
	}
}