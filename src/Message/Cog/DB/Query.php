<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
* Query
*/
class Query
{
	protected $_connection;
	protected $_params;
	protected $_query;

	const TOKEN_REGEX = '/((\:[a-zA-Z0-9_\-\.]*)|\?)(\|([a-z]+))?/';

	public function __construct(ConnectionInterface $connection)
	{
		$this->setConnection($connection);
	}

	public function run($query, $params = array())
	{
		$this->_query  = $query;
		$this->_params = (array)$params;

		$this->_parseParams();
		$result = $this->_connection->query($this->_query);

		if($result === false) {
			throw new Exception($this->_connection->getLastError(), $this->_query);
		}

		return new Result($result, clone $this);
	}

	public function setConnection(ConnectionInterface $connection)
	{
		$this->_connection = $connection;
	}

	private function _parseParams()
	{
		if(!count($this->_params)) {
			return false;
		}

		$counter = 0;
		$types = array(
			's'	=> 'string',
			'i'	=> 'integer',
			'f' => 'float',
			'd'	=> 'datetime',
		);

		// PHP 5.3 hack
		$connection = $this->_connection;
		$fields = $this->_params;
		$this->_query = preg_replace_callback(self::TOKEN_REGEX, function($matches) use($fields, $types, &$counter, $connection) {

			// parse and validate the token
			$full  = $matches[0];
			$param = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : false;
			$flags = isset($matches[6]) ? $matches[6] : 'sn';
			$type  = str_replace('n', '', $flags, $useNull);

			if(!isset($types[$type])) {
				throw new Exception(sprintf('Unknown type `%s` in token `%s`', $type, $full));
			}

			// decide what data to use
			$data = null;
			if($param !== false && isset($fields[$param])) {
				$data = $fields[$param];
			} else if($param === false  && $counter < count($fields)) {
				$data = array_slice($fields, $counter, 1);
				$data = reset($data);
			}
			$counter++;

			// check for nullness
			if(is_null($data) && $useNull) {
				return 'NULL';
			}

			// santize
			settype($data, $types[$type]);
			$safe = $connection->escape($data);

			// format it ready for the query
			if($type == 'd') {
				if(ctype_digit($safe)) {
					$safe = 'FROM_UNIXTIME('.$safe.')';
				} else {
					$safe = "'".$safe."'";
				}
			} elseif($type == 's' || $type == 'f') {
				$safe = "'".$safe."'";
			}

			return $safe;
		}, $this->_query);

		return true;
	}


}