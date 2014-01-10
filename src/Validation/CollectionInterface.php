<?php

namespace Message\Cog\Validation;

/**
 * Class CollectionInterface
 * @package Message\Cog\Validation
 *
 * Interface for creating collections for data to be parsed to for validation
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
interface CollectionInterface
{
	/**
	 * @param Loader $loader
	 *
	 * @return mixed
	 */
	public function register(Loader $loader);
}