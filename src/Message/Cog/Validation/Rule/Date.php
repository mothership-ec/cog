<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Date rule
 * @package Message\Cog\Validation\Rule
 *
 * Class used to validating and comparing dates.
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Date implements CollectionInterface
{
	/**
	 * Register rules to Loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('before', array($this, 'before'), '%s must%s be before %s.')
			->registerRule('after', array($this, 'after'), '%s must%s be after %s.');
	}

	/**
	 * Checks that an inputted date is set before a pre-determined date.
	 *
	 * @param \DateTime $var        The variable to validate
	 * @param \DateTime $target     The target date that $var must not be later than
	 * @param bool $orEqualTo       If set to true, $var may be equal to $target
	 *
	 * @return bool                 Returns true if $var is before $target
	 */
	public function before(\DateTime $var, \DateTime $target, $orEqualTo = false)
	{
		if ($orEqualTo) {
			return $var <= $target;
		}
		return $var < $target;
	}

	/**
	 * Checks that an inputted date is set after a pre-determined date.
	 *
	 * @param \DateTime $var        The variable to validate
	 * @param \DateTime $target     The target date that $var must be later than
	 * @param bool $orEqualTo       If set to true, $var may be equal to $target
	 *
	 * @return bool                 Returns true if $var is after $target
	 */
	public function after(\DateTime $var, \DateTime $target, $orEqualTo = false)
	{
		if ($orEqualTo) {
			return $var >= $target;
		}
		return $var > $target;
	}
}