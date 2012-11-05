<?php

namespace Message\Cog\DB\Connection;

/**
*
*/
interface Connection
{
	public function __construct(array $params);
	public function query($sql);
	public function escape($text);
	public function getLastError();
	public function getAffectedRows();
	public function getLastInsertId();
}