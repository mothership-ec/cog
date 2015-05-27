<?php

namespace Message\Cog\DB\Adapter\MySQLi;

use Message\Cog\DB\Adapter\ConnectionInterface;
use Message\Cog\DB\Adapter\CachableInterface;
use Message\Cog\DB\Adapter\CacheInterface;
use Message\Cog\DB\Adapter\QueryCountableInterface;
use Message\Cog\DB\Adapter\Exception;

/**
*
*/
class Connection implements ConnectionInterface, CachableInterface, QueryCountableInterface
{
	protected $_handle = null;
	protected $_params = array();

	private $_queryList = [];

	private $_cacheEnabled = true;

	/**
	 * @var CacheInterface
	 */
	private $_cache;

	/**
	 * Required parameters for database connection
	 *
	 * @var array
	 */
	private $_required = [
		'host',
		'user',
		'db',
	];

	public function __construct(array $params = array())
	{
		$this->_params = $params;

		if(isset($this->_params['lazy']) && $this->_params['lazy'] === false) {
			$this->_connect();
		}
	}

	/**
	 * Disconnect when serializing connection
	 *
	 * @return array
	 */
	public function __sleep()
	{
		$this->_handle = null;

		return [
			'_params',
			'_queryList',
			'_cacheEnabled',
		];
	}

	/**
	 * Re-establish database connection upon unserialization if the `lazy` parameter is set to false
	 */
	public function __wakeup()
	{
		if (isset($this->_params['lazy']) && $this->_params['lazy'] === false) {
			$this->_connect();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCache(CacheInterface $cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * {@inheritDoc}
	 */
	public function query($sql)
	{
		if ($this->_cacheEnabled() && $this->_cache->resultInCache($sql)) {
			return $this->_cache->getCachedResult($sql);
		}

		$this->_connect();

		if($res = $this->_handle->query($sql)) {
			$result = new Result($res, $this);

			if ($this->_cacheEnabled()) {
				$this->_cache->cacheResult($sql, $result);
			}

			$this->_queryList[] = $sql;

			return $result;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function escape($text)
	{
		$this->_connect();

		return $this->_handle->real_escape_string($text);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastError()
	{
		$this->_connect();

		return $this->_handle->error;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHandle()
	{
		return $this->_handle;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTransactionStart()
	{
		return 'START TRANSACTION';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTransactionEnd()
	{
		return 'COMMIT';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTransactionRollback()
	{
		return 'ROLLBACK';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastInsertIdFunc()
	{
		return 'LAST_INSERT_ID()';
	}

	public function getQueryCount()
	{
		return count($this->_queryList);
	}

	public function getQueryList()
	{
		return $this->_queryList;
	}

	public function enableCache()
	{
		$this->_cacheEnabled = true;
	}

	public function disableCache()
	{
		$this->_cacheEnabled = false;
	}

	protected function _connect()
	{
		// If we've already got a connection handle we don't
		// need to connect again
		if($this->_handle !== null) {
			return;
		}

		$this->_validateParams();

		// Make the connection
		$this->_handle = new \MySQLi(
			$this->_params['host'],
			$this->_params['user'],
			$this->_params['password'],
			$this->_params['db']
		);

		// Set to natively return integer & float types where appropriate
		if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
			$this->_handle->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
		}

		// Set the charset
		if(isset($this->_params['charset'])) {
			$this->_handle->set_charset($this->_params['charset']);
		}

		// Set the timezone to match PHP's
		$now = new \DateTime;
		$offset = $now->format('P');
		$this->query('SET time_zone="' . $offset . '";');
	}

	/**
	 * {@inheritDoc}
	 */
	private function _cacheEnabled()
	{
		return $this->_cacheEnabled && $this->_cache;
	}

	private function _validateParams()
	{
		foreach ($this->_required as $required) {
			if (!array_key_exists($required, $this->_params)) {
				throw new Exception\InvalidParameterException('Missing required database parameter `' . $required . '`, please add to db.yml config');
			}
		}
	}

}