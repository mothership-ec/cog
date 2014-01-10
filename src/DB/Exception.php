<?php

namespace Message\Cog\DB;

/**
*
*/
class Exception extends \Exception
{
	protected $_sql = null;

	public function __construct($message, $sql, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->_sql = $sql;
	}

	public function getQuery()
	{
		return $this->_sql;
	}
}
