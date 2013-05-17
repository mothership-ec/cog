<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
 * Number rule
 * @package Message\Cog\Validation\Rule
 *
 * Validating numeric values
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Number implements CollectionInterface
{
	/**
	 * Register rules
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('min', array($this, 'min'), '%s must%s be equal to or greater than %s.')
			->registerRule('max', array($this, 'max'), '%s must%s be less than or equal to %s.')
			->registerRule('between', array($this, 'between'), '%s must%s be between %s and %s.');
	}

	/**
	 * Checks that variable is above the minumum
	 *
	 * @param int|float|string $var     The variable to validate
	 * @param int|float|string $min     The minimum that $var can be
	 *
	 * @return bool                     Returns true if $var is greater than or equal to $min
	 */
	public function min($var, $min)
	{
		CheckType::checkNumeric($var, '$var');
		CheckType::checkNumeric($min, '$min');

		return $var >= $min;
	}

	/**
	 * Checks that variable is below the maximum
	 *
	 * @param int|float|string $var     The variable to validate
	 * @param int"float|string $max     The minimum that $var can be
	 *
	 * @return bool                     Returns true if $var is less than or equal to $max
	 */
	public function max($var, $max)
	{
		CheckType::checkNumeric($var, '$var');
		CheckType::checkNumeric($max, '$max');

		return $var <= $max;
	}

	/**
	 * Checks that variable falls between two numbers
	 *
	 * @param int|float|string $var     The variable to validate
	 * @param int|float|string $min     The minimum that $var can be
	 * @param int|float|string $max     The maximum that $var can be
	 * @throws \Exception               Throws exception if $min is greater than $max
	 *
	 * @return bool                     Returns true if $var falls between $min and $max
	 */
	public function between($var, $min, $max)
	{
		CheckType::checkNumeric($var, '$var');
		CheckType::checkNumeric($min, '$min');
		CheckType::checkNumeric($max, '$max');

		if ($min >= $max) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $max must be greater than $min');
		}

		return $this->min($var, $min) && $this->max($var, $max);
	}

}