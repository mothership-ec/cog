<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Validator;
use Message\Cog\Validation\Loader;

/**
* Rules
*/
class Iterable implements CollectionInterface
{
	/**
	 * @param Loader $loader
	 * @return mixed|void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('each', array($this, 'each'), '%s must%s be valid.')
			->registerRule('validateEach', array($this, 'validateEach'), '%s must%s be valid.');
	}

	/**
	 * @param array $var
	 * @param $func
	 * @return bool
	 */
	public function each(array $var, $func)
	{
		return ($this->_isAssoc($var)) ? $this->_eachAssoc($var, $func) : $this->_eachSeq($var, $func);
	}

	/**
	 * @param array $var
	 * @param $func
	 * @return bool
	 * @throws \Exception
	 */
	public function validateEach(array $var, $func)
	{
		$validator = new Validator();
		$validator = $func($validator);

		if(!($validator instanceof Validator)) {
			throw new \Exception('Callback must return an instance of Validator');
		}

		foreach($var as $key => $value) {
			if(!$validator->validate($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if array is associative or sequential
	 *
	 * @param $arr array
	 * @return bool
	 */
	protected function _isAssoc(array $arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * Takes into account the keys and pass them as a second parameter to the function
	 *
	 * @param array $var
	 * @param $func
	 * @return bool
	 */
	protected function _eachAssoc($var, $func)
	{
		foreach($var as $key => $value) {
			if(!$func($value, $key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Ignores keys and only pass the value to the function
	 *
	 * @param array $var
	 * @param $func
	 * @return bool
	 */
	protected function _eachSeq($var, $func)
	{
		foreach($var as $value) {
			if(!$func($value)) {
				return false;
			}
		}

		return true;
	}

}