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

	/**
	 * @param \DateTime $var
	 * @param \DateTime $target
	 * @param bool $orEqualTo
	 * @return bool
	 */
	public function before(\DateTime $var, \DateTime $target, $orEqualTo = false)
	{
		if ($orEqualTo) {
			return $var <= $target;
		}
		return $var < $target;
	}

	/**
	 * @param \DateTime $var
	 * @param \DateTime $target
	 * @param bool $orEqualTo
	 * @return bool
	 */
	public function after(\DateTime $var, \DateTime $target, $orEqualTo = false)
	{
		if ($orEqualTo) {
			return $var >= $target;
		}
		return $var > $target;
	}
}