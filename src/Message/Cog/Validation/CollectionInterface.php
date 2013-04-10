<?php

namespace Message\Cog\Validation;

interface CollectionInterface
{
	/**
	 * @param Loader $loader
	 * @return mixed
	 */
	public function register(Loader $loader);
}