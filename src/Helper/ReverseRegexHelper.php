<?php

namespace Message\Cog\Helper;

use ReverseRegex\Lexer;
use ReverseRegex\Random\SimpleRandom;
use ReverseRegex\Parser;
use ReverseRegex\Generator\Scope;

class ReverseRegexHelper
{
	public function getString($regex)
	{
		$string = '';

		$this->_getParser($regex)
			->parse()
			->getResult()
			->generate($string, new SimpleRandom)
		;

		return $string;
	}

	private function _getParser($regex)
	{
		if (!is_string($regex)) {
			throw new \InvalidArgumentException('Regex must be a string!');
		}

		return new Parser(
			new Lexer($regex),
			new Scope()
		);
	}
}