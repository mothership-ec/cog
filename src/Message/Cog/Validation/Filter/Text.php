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
			->registerFilter('replace',         array($this, 'replace'))
			->registerFilter('url',             array($this, 'url'));
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
	 * This is a copy of Message\Cog\Functions\Text::toTitleCase, with the $maintainCase argument added in. In time one of these will be obsolete
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

		$text = join("'", array_map('ucwords', explode("'", $text)));
		$text = join("-", array_map('ucwords', explode("-", $text)));
		$text = join("(", array_map('ucwords', explode("(", $text)));
		$text = join("Mac", array_map('ucwords', explode("Mac", $text)));
		$text = join("Mc", array_map('ucwords', explode("Mc", $text)));

		$ignores = array(
			' a ',
			' or ',
			' if ',
			' it ',
			' and ',
			' or ',
			' nor ',
			' but ',
			' so ',
			' is ',
			' the ',
			' are ',
			' on ',
			' in ',
			' of ',
		);

		$text = str_replace(array_map('ucwords', $ignores), $ignores, $text);

		return $text;
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
	 * @param bool $maintainCase
	 * @return string
	 */
	public function capitalize($text, $maintainCase = false)
	{
		$this->_checkString($text);

		if (!$maintainCase) {
			$text = strtolower($text);
		}

		$text = explode(' ', $text);

		foreach ($text as &$word) {
			$word = ucfirst($word);
		}

		return implode(' ', $text);
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
	 * Checks URL has correct protocol, with optional to replace existing e.g. http:// with https://
	 * Regex only checks for http, https, or ftp at the moment
	 *
	 * @param $url
	 * @param string $protocol
	 * @param bool $replaceExisting
	 * @return mixed|string
	 */
	public function url($url, $protocol = 'http', $replaceExisting = false)
	{
		$this->_checkString($url)
			->_checkString($protocol);

		$pattern = "~^(?:f|ht)tps?://~i";

		if (!preg_match($pattern, $url)) {
			$url = $protocol . '://' . $url;
		} elseif ($replaceExisting) {
			$url = preg_replace($pattern, $protocol . '://', $url);
		}

		return $url;
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