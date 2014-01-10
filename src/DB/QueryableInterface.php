<?php

namespace Message\Cog\DB;

/**
 * Interface defining something that is queryable: a database query can be run
 * on it.
 *
 * Both the regular `Query` and the `Transaction` objects should implement this.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface QueryableInterface
{
	public function run($query, $params = array());
}