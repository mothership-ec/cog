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
		$loader->registerFilter('uppercase',    array($this, 'uppercase'))
			->registerFilter('lowercase',       array($this, 'lowercase'))
			->registerFilter('titlecase',       array($this, 'titlecase'))
			->registerFilter('prefix',          array($this, 'prefix'))
			->registerFilter('suffix',          array($this, 'suffix'))
			->registerFilter('trim',            array($this, 'trim'))
			->registerFilter('rtrim',           array($this, 'rtrim'))
			->registerFilter('ltrim',           array($this, 'ltrim'))
			->registerFilter('capitalize',      array($this, 'capitalize'))
			->registerFilter('replace',         array($this, 'replace'));
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
	 * @param string $text
	 * @param null $chars
	 * @return string
	 */
	public function trim($text, $chars = null)
	{
		if (!$chars) {
			return trim($text);
		}

		return trim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param null $chars
	 * @return string
	 */
	public function rtrim($text, $chars = null)
	{
		if (!$chars) {
			return rtrim($text);
		}

		return rtrim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param null $chars
	 * @return string
	 */
	public function ltrim($text, $chars = null)
	{
		if (!$chars) {
			return ltrim($text);
		}

		return ltrim($text, $chars);
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