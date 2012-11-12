<?php

namespace Message\Cog\Functions;

class Debug
{
	/**
	 * Outputs the current variable using `var_dump` and exits the script.
	 *
	 * @param mixed $var The variable to dump
	 */
	public function dump($var)
	{
		var_dump($var);
		exit;
	}
}