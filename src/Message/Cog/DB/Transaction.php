<?php

namespace Message\Cog\DB;

/**
*
*/
class Transaction extends Query
{
	protected $_queries = array();

	public function add($query, $params = array())
	{
		$this->_queries[] = array($query, $params);

		return $this;
	}

	public function rollback()
	{
		return $this->run($this->_connection->getTransactionRollback());
	}

	public function commit()
	{
		$this->run($this->_connection->getTransactionStart());
		try {
			foreach($this->_queries as $query) {
				$this->run($query[0], $query[1]);
			}
		} catch(Exception $e) {
			$this->rollback();
			throw $e;
		}

		return $this->run($this->_connection->getTransactionEnd());
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