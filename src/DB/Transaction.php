<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Event\Event;

/**
 * A transaction is a collection of queries that run one after another as a
 * batch. If an error occurs while executing them, all of the queries in the
 * transaction are rolled back. This helps prevent malformed databases.
 */
class Transaction implements QueryableInterface
{
	protected $_connection;
	protected $_query;
	protected $_queries = [];

	protected $_eventDispatcher;
	protected $_events = [];

	/**
	 * Constructor
	 *
	 * @param ConnectionInterface $connection The database connection to use
	 * @param DispatcherInterface $dispatcher The event dispatcher to use
	 */
	public function __construct(ConnectionInterface $connection, QueryParser $parser, DispatcherInterface $dispatcher)
	{
		$this->_eventDispatcher = $dispatcher;
		$this->_connection      = $connection;

		$this->_query = new Query($connection, $parser);
		$this->_query->fromTransaction = true;
	}

	/**
	 * Attach an event to the database transaction. The event will be dispatched
	 * after the query has been committed, assuming the transaction was not
	 * rolled back.
	 *
	 * If the transaction was rolled back, no events are dispatched.
	 *
	 * It is also possible to pass a Closure in for the second parameter
	 * `$event`. This closure will be given this transaction as the first and
	 * only parameter, and must return an instance of Event. The closure is
	 * executed just before the event is dispatched.
	 *
	 * @param  string         $name  The name of the event to dispatch
	 * @param  Event|\Closure $event The event object to dispatch, or a closure
	 *                               that returns the event object
	 *
	 * @return Transaction           Returns $this for chainability
	 */
	public function attachEvent($name, $event)
	{
		if (!($event instanceof Event)
		 && !($event instanceof \Closure)) {
			throw new \InvalidArgumentException('Cannot attach event: expected Event or Closure instance');
		}

		$this->_events[] = [
			'name'  => $name,
			'event' => $event,
		];

		return $this;
	}

	public function add($query, $params = [])
	{
		$this->_queries[] = [$query, $params];

		return $this;
	}

	public function run($query, $params = [])
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
			foreach ($this->_queries as $query) {
				$this->_query->run($query[0], $query[1]);
			}
		} catch (Exception $e) {
			$this->rollback();
			throw $e;
		}

		$return = $this->_query->run($this->_connection->getTransactionEnd());

		$this->_dispatchEvents();

		$this->reset();

		return $return;
	}

	/**
	 * Reset the transaction to an empty state.
	 *
	 * All events and queries are removed.
	 *
	 * @return Transaction Returns $this for chainability
	 */
	public function reset()
	{
		$this->_events  = [];
		$this->_queries = [];

		return $this;
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

	/**
	 * Dispatch all events set on this transaction.
	 *
	 * Any event definitions that were passed in as closures are executed, and
	 * the result is dispatched.
	 */
	protected function _dispatchEvents()
	{
		foreach ($this->_events as $event) {
			$eventInstance = ($event['event'] instanceof \Closure)
				? $event['event']($this)
				: $event['event'];

			$this->_eventDispatcher->dispatch($event['name'], $eventInstance);
		}
	}
}