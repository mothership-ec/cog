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

	public function commit()
	{
		$this->run('BEGIN');
		try {
			foreach($this->_queries as $query) {
				$this->run($query[0], $query[1]);
			}
		} catch(Exception $e) {
			$this->rollback();
			throw $e;
		}

		return $this->query('COMMIT');
	}

	public function rollback()
	{
		$this->run('ROLLBACK');
	}
}