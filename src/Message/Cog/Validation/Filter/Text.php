<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;

/**
* Filters
*/
class Text implements CollectionInterface
{
	public function register($loader)
	{
		$loader->registerFilter('uppercase',  array($this, 'uppercase'));
		$loader->registerFilter('lowercase',  array($this, 'lowercase'));
		$loader->registerFilter('prefix',     array($this, 'prefix'));
		$loader->registerFilter('suffix',     array($this, 'suffix'));
		$loader->registerFilter('trim',       array($this, 'trim'));
		$loader->registerFilter('capitalize', array($this, 'capitalize'));
	}

	public function uppercase($text)
	{
		return strtoupper($text);
	}

	public function lowercase($text)
	{
		return strtolower($text);
	}

	public function titlecase($text)
	{
		return ucwords($text);
	}

	public function prefix($text, $prefix)
	{
		return $prefix.$text;
	}

	public function suffix($text, $suffix)
	{
		return $text.$suffix;
	}

	public function capitalize($text)
	{
		return ucfirst($text);
	}

	public function replace($text, $search, $replace)
	{
		return str_replace($search, $replace, $text);
	}
}