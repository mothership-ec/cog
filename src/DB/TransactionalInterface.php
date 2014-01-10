<?php

namespace Message\Cog\DB;

/**
 * Interface for classes that can be transactional.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface TransactionalInterface
{
	/**
	 * Set the transaction to use.
	 *
	 * @param Transaction $trans The transaction
	 */
	public function setTransaction(Transaction $trans);
}