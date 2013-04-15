<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
* Filters
*/
class Text implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerFilter('uppercase', array($this, 'uppercase'))
			->registerFilter('lowercase', array($this, 'lowercase'))
			->registerFilter('titlecase', array($this, 'titlecase'))
			->registerFilter('prefix', array($this, 'prefix'))
			->registerFilter('suffix', array($this, 'suffix'))
			->registerFilter('trim', array($this, 'trim'))
			->registerFilter('rtrim', array($this, 'rtrim'))
			->registerFilter('ltrim', array($this, 'ltrim'))
			->registerFilter('capitalize', array($this, 'capitalize'))
			->registerFilter('replace', array($this, 'replace'))
			->registerFilter('url', array($this, 'url'))
			->registerFilter('slug', array($this, 'slug'))
			;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function uppercase($text)
	{
		CheckType::checkString($text, '$text');

		return strtoupper($text);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function lowercase($text)
	{
		CheckType::checkString($text, '$text');

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
		CheckType::checkString($text, '$text');

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
		CheckType::checkStringOrNumeric($text, '$text');
		CheckType::checkStringOrNumeric($delim, '$delim');
		CheckType::checkStringOrNumeric($prefix, '$prefix');

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
		CheckType::checkStringOrNumeric($text, '$text');
		CheckType::checkStringOrNumeric($delim, '$delim');
		CheckType::checkStringOrNumeric($suffix, '$suffix');

		return $text . $delim . $suffix;
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function trim($text, $chars = null)
	{
		CheckType::checkString($text, '$text');

		if (!$chars) {
			return trim($text);
		}

		CheckType::checkString($chars, '$chars');

		return trim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function rtrim($text, $chars = null)
	{
		CheckType::checkString($text, '$text');

		if (!$chars) {
			return rtrim($text);
		}

		CheckType::checkString($chars, '$chars');

		return rtrim($text, $chars);
	}

	/**
	 * @param string $text
	 * @param string | null $chars
	 * @return string
	 */
	public function ltrim($text, $chars = null)
	{
		CheckType::checkString($text, '$text');

		if (!$chars) {
			return ltrim($text);
		}

		CheckType::checkString($chars, '$chars');

		return ltrim($text, $chars);
	}

	/**
	 * @param $text
	 * @param bool $maintainCase
	 * @return string
	 */
	public function capitalize($text, $maintainCase = false)
	{
		CheckType::checkString($text, '$text');

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
		CheckType::checkStringOrNumeric($text, '$text');
		CheckType::checkStringOrNumeric($search, '$search');
		CheckType::checkStringOrNumeric($replace, '$replace');

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
		CheckType::checkString($url, '$url');
		CheckType::checkString($protocol, '$protocol');

		$pattern = "~^(?:f|ht)tps?://~i";

		$protocol = str_replace('://', '', $protocol);

		if (!preg_match($pattern, $url)) {
			$url = $protocol . '://' . $url;
		} elseif ($replaceExisting) {
			$url = preg_replace($pattern, $protocol . '://', $url);
		}

		return $url;
	}

	/**
	 * Taken from http://sourcecookbook.com/en/recipes/8/function-to-slugify-strings-in-php
	 *
	 * @param $text
	 * @return string
	 */
	public function slug($text)
	{
		CheckType::checkStringOrNumeric($text);

		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		$text = trim($text, '-');
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = strtolower($text);
		$text = preg_replace('~[^-\w]+~', '', $text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
}