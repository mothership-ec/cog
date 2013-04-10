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
	 * @param string $text
	 * @return string
	 */
	public function uppercase($text)
	{
		$this->_checkString($text);

		return strtoupper($text);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function lowercase($text)
	{
		$this->_checkString($text);

		return strtolower($text);
	}

	/**
	 * @todo make so it's not the same as capitalize
	 *
	 * @param string $text
	 * @param bool $maintainCase
	 * @return string
	 */
	public function titlecase($text, $maintainCase = false)
	{
		$this->_checkString($text);

		if(!$maintainCase){
			$text = strtolower($text);
		}
		return ucwords($text);
	}

	/**
	 * @param $text
	 * @param $prefix
	 * @param string $delim
	 * @return string
	 */
	public function prefix($text, $prefix, $delim = '')
	{
		$this->_checkString($text)
			->_checkString($delim)
			->_checkString($prefix);

		return $prefix . $delim . $text;
	}

	/**
	 * @param $text
	 * @param $suffix
	 * @param string $delim
	 * @return string
	 */
	public function suffix($text, $suffix, $delim = '')
	{
		$this->_checkString($text)
			->_checkString($delim)
			->_checkString($suffix);

		return $text . $delim . $suffix;
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function trim($text, $chars = null)
	{
		$this->_checkString($text);

		if (!$chars) {
			return trim($text);
		}

		$this->_checkString($chars);

		return trim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function rtrim($text, $chars = null)
	{
		$this->_checkString($text);

		if (!$chars) {
			return rtrim($text);
		}

		$this->_checkString($chars);

		return rtrim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function ltrim($text, $chars = null)
	{
		$this->_checkString($text);

		if (!$chars) {
			return ltrim($text);
		}

		$this->_checkString($chars);

		return ltrim($text, $chars);
	}

	/**
	 * @param $text
	 * @return string
	 */
	public function capitalize($text)
	{
		$this->_checkString($text);

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
		$this->_checkString($text)
			->_checkString($search)
			->_checkString($replace);

		return str_replace($search, $replace, $text);
	}

	/**
	 * Confirm that method has been given a string, should be used in all text methods
	 *
	 * @param $string
	 * @return $this
	 * @throws \Exception
	 */
	protected function _checkString($string)
	{
		if (is_int($string)) {
			$string = (string) $string;
		}

		if (!is_string($string)) {
			$callers = debug_backtrace();
			throw new \Exception(__CLASS__ . '::' . $callers[1]['function'] . ' - $string param must be a string, ' . gettype($string) . ' given');
		}

		return $this;
	}
}