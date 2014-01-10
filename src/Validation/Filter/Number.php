<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

class Number implements CollectionInterface
{
	/**
	 * Register filters to loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerFilter('add',  array($this, 'add'))
			->registerFilter('subtract', array($this, 'subtract'))
			->registerFilter('multiply', array($this, 'multiply'))
			->registerFilter('divide', array($this, 'divide'))
			->registerFilter('percentage', array($this, 'percentage'))
		;
	}

	/**
	 * Add a number to a variable
	 *
	 * @param int|float|string $var     Value to filter
	 * @param int|float|string $value   Value to add to $var
	 * @throws \Exception               Throws exception if $value is not numeric
	 *
	 * @return int|float
	 */
	public function add($var, $value)
	{
		if (!is_numeric($value)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $value must be numeric');
		}

		return $var + $value;
	}

	/**
	 * Subtract a number from a variable
	 *
	 * @param int|float|string $var         Value to filter
	 * @param int|float|string $value       Value to subtract
	 * @throws \Exception                   Throws exception if $value is not numeric
	 *
	 * @return int|float
	 */
	public function subtract($var, $value)
	{
		if (!is_numeric($value)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $value must be numeric');
		}

		return $var - $value;
	}

	/**
	 * Multiply a variable by a number
	 *
	 * @param int|float|string $var         Value to filter
	 * @param int|float|string $value       Value to multiply $var by
	 * @throws \Exception                   Throws exception if $value is not numeric
	 *
	 * @return int|float
	 */
	public function multiply($var, $value)
	{
		if (!is_numeric($value)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $value must be numeric');
		}

		return $var * $value;
	}

	/**
	 * Divide a variable by a number
	 *
	 * @param int|float|string $var         Value to filter
	 * @param int|float|string $value       Value to divide $var by
	 * @throws \Exception                   Throws exception if $value is not numeric
	 *
	 * @return float
	 */
	public function divide($var, $value)
	{
		if (!is_numeric($value)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $value must be numeric');
		}

		return $var / $value;
	}

	/**
	 * Get the percentage of a variable of a number
	 *
	 * @param int|float|string $var         Value to filter
	 * @param int|float|string $value       The 100% value
	 * @throws \Exception                   Throws exception if $value is not numeric
	 *
	 * @return float
	 */
	public function percentage($var, $value)
	{
		if (!is_numeric($value)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $value must be numeric');
		}

		return ($var / $value) * 100;
	}

}