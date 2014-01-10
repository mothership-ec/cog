<?php

namespace Message\Cog\DB\Adapter;

/**
*
*/
interface ConnectionInterface
{
	public function __construct(array $params = array());
	public function query($sql);
	public function escape($text);
	public function getLastError();
	public function getTransactionStart();
	public function getTransactionEnd();
	public function getTransactionRollback();
	public function getLastInsertIdFunc();
}