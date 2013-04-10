<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Date rules
*/
class Date implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('before', array($this, 'before'), '%s must%s be before %s.')
			->registerRule('after', array($this, 'after'), '%s must%s be after %s.');
	}

	public function before(\DateTime $var, \DateTime $target)
	{
		return $var < $target;
	}

	public function after(\DateTime $var, \DateTime $target)
	{
		return $var > $target;
	}
}