<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Rules
*/
class Number implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('min', array($this, 'min'), '%s must%s be equal to or greater than %s.');
		$loader->registerRule('max', array($this, 'max'), '%s must%s be less than or equal to %s.');
		$loader->registerRule('between', array($this, 'between'), '%s must%s be between %s and %s.');
	}

	public function min($var, $min)
	{
		return $var >= $min;
	}

	public function max($var, $max)
	{
		return $var <= $max;
	}

	public function between($var, $min, $max)
	{
		return $this->min($min) && $this->max($max);
	}

}