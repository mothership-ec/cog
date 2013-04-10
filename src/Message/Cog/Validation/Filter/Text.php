<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Filters
*/
class Text implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerFilter('uppercase',  array($this, 'uppercase'));
		$loader->registerFilter('lowercase',  array($this, 'lowercase'));
		$loader->registerFilter('prefix',     array($this, 'prefix'));
		$loader->registerFilter('suffix',     array($this, 'suffix'));
		$loader->registerFilter('trim',       array($this, 'trim'));
		$loader->registerFilter('capitalize', array($this, 'capitalize'));
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function uppercase($text)
	{
		return strtoupper($text);
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function lowercase($text)
	{
		return strtolower($text);
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function titlecase($text)
	{
		return ucwords($text);
	}

	/**
	 * @param $text
	 * @param $prefix
	 * @return string
	 */
	public function prefix($text, $prefix)
	{
		return $prefix.$text;
	}

	/**
	 * @param $text
	 * @param $suffix
	 * @return string
	 */
	public function suffix($text, $suffix)
	{
		return $text.$suffix;
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function capitalize($text)
	{
		return ucfirst($text);
	}

	/**
	 * @param $text
	 * @param $search
	 * @param $replace
	 * @return mixed
	 */
	public function replace($text, $search, $replace)
	{
		return str_replace($search, $replace, $text);
	}
}