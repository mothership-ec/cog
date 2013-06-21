<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\Loader;
use Message\Cog\Validation\CollectionInterface;

/**
 * Other rule
 * @package Message\Cog\Validation\Rule
 *
 * Can use callables such as native PHP functions to validate inputs
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Other implements CollectionInterface
{
	/**
	 * @var Loader
	 */
	protected $_loader;
	/**
	 * Register rules to loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('rule', array($this, 'rule'), '%s must%s pass a custom rule.');
		$this->_loader = $loader;
	}

	/**
	 * @param mixed $var        The variable to validate
	 * @param string $func      A callable function to use to validate $var
	 * @throws \Exception       Throws exception if $func is not callable
	 *
	 * @return mixed
	 */
	public function rule($var, $func)
	{
		$data = $this->_loader->getValidator()->getData();

		if (is_callable($func)) {
			return call_user_func($func, $var, $data);
		}
		else {
			throw new \Exception (__CLASS__ . '::' . __METHOD__ . ' - $func must be callable');
		}
	}
}