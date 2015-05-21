<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
* Query Parser class
*
*/
class QueryParser
{

	const TOKEN_REGEX = '/((\:[a-zA-Z0-9_\-\.]*)\??([a-z]*)?)|(\?([a-z]*))/us';

	/**
	 * @var ConnectionInterface
	 */
	private $_connection;

	protected $_typeTokens = array(
		's' => 'string',
		'i' => 'integer',
		'f' => 'float',
		'd'	=> 'datetime',
		'q' => 'sub_query', // must be QueryBuilderInterface
		'b'	=> 'boolean',
	);

	public function __construct(ConnectionInterface $connection)
	{
		$this->setConnection($connection);
	}

	/**
	 * Set the connection to use for this query. Useful if you want to run the
	 * same query against multiple connections.
	 *
	 * @param ConnectionInterface $connection
	 */
	public function setConnection(ConnectionInterface $connection)
	{
		$this->_connection = $connection;
	}

	public function parse($statement, array $variables)
	{
		if(empty($variables)) {
			return $statement;
		}

		$connection = $this->_connection;
		$fields     = $variables;
		$types      = $this->_typeTokens;
		$self       = $this;
		$query 		= $statement;

		$counter = 0;

		$parsedQuery = preg_replace_callback(
			self::TOKEN_REGEX,
			function($matches) use($self, $fields, $types, &$counter, $connection, $query) {

				// parse and validate the token
				$full  = $matches[0];
				$param = substr($full, 0, 1) == ':' ? substr($matches[2], 1) : false; // The var after the colon.
				$flagIndex = $param === false ? 5 : 3;
				$flags = $matches[$flagIndex] ?: 'sn'; // data casting flags
				$type  = str_replace('n', '', $flags, $useNull);
				$type  = str_replace('j', '', $type, $useJoin);

				if(!isset($types[$type])) {
					throw new Exception(sprintf('Unknown type `%s` in token `%s`', $type, $full), $query);
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

				if ($useJoin) {
					if (!is_array($data)) {
						throw new Exception(
							sprintf('Cannot use join in token `%s` as it is not an array.', $full),
							$query
						);
					}

					foreach ($data as $key => $value) {
						$data[$key] = $self->castValue($value, $type, $useNull);
					}

					return implode(', ', $data);
				}

				return $self->castValue($data, $type, $useNull);
			},
		$statement);

		return $parsedQuery;
	}

	public function castValue($value, $type, $useNull)
	{
		// check for nullness
		if (is_null($value) && $useNull) {
			return 'NULL';
		}

		if ($value instanceof \DateTime) {
			$value = $value->getTimestamp();
		}

		// If a type is set to date then cast it to an int
		if ($type === 'd') {
		    $safe = (int) $value;
		} elseif ($type === 'q') {
			if (!$value instanceof QueryBuilderInterface) {
				$valueType = gettype($value) === 'object' ? get_class($value) : gettype($value);
				throw new \InvalidArgumentException('Cannot parse value as sub query (?q) as value must be an instance of QueryBuilderInterface, ' . $valueType . ' given');
			}
			$safe = $value->getQueryString();
		} else {
			// Don't cast type if type is integer and value starts with @ (as it is an ID variable)
			if (!('i' === $type && '@' === substr($value, 0, 1))) {
				settype($value, $this->_typeTokens[$type]);
			}
			$safe = $this->_connection->escape($value);
		}
		// Floats are quotes to support all locales.
		// See: http://stackoverflow.com/questions/2030684/which-mysql-data-types-should-i-not-be-quoting-during-an-insert"
		if ($type === 's' || $type === 'f') {
			$safe = "'".$safe."'";
		}

		if ('b' === $type) {
			$safe = $value ? 1 : 0;
		}

		return $safe;
	}
}