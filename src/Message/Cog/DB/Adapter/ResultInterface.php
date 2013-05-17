<?php

namespace Message\Cog\DB\Adapter;

/**
*
*/
interface ResultInterface
{
	public function __construct($handle, ConnectionInterface $connection);
	public function fetchArray();
	public function fetchObject();
	public function seek($position);
	public function getAffectedRows();
	public function getLastInsertId();
	public function getTransactionStart();
	public function getTransactionEnd();
	public function getTransactionRollback();
	public function getLastInsertIdFunc();
}
