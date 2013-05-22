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
}