<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Functions\Text as TextFunction;

/**
 * Text filters.
 * @package Message\Cog\Validation\Filter
 *
 * Casts fields through string filters
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Text implements CollectionInterface
{

	/**
	 * Register filters to the loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
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
			->registerFilter('toUrl', array($this, 'toUrl'))
			->registerFilter('slug', array($this, 'slug'))
			;
	}

	/**
	 * Filter to convert text to uppercase
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function uppercase($text)
	{
		return strtoupper($text);
	}

	/**
	 * Filter to convert text to lowercase
	 *
	 * @param string $text  The variable to be cast
	 *
	 * @return string
	 */
	public function lowercase($text)
	{
		return strtolower($text);
	}

	/**
	 * This is a copy of Message\Cog\Functions\Text::toTitleCase, with the $maintainCase argument added in. In time one
	 * of these will be obsolete.
	 *
	 * Note: This does not capitalize strings, it ignores 'minor' words such as conjunctions (unless it is the first
	 * word of the string). See $ignores array defined in method to see which words get ignored.
	 *
	 * @param string $text          The variable to be cast
	 * @param bool $maintainCase    If set to false, string will be converted to lowercase before the comment is run.
	 *
	 * @return string
	 */
	public function titlecase($text, $maintainCase = false)
	{
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
	 * Prepends a string onto another string
	 *
	 * @param $text             The variable to be cast
	 * @param $prefix           Text to be attached to $text
	 * @param string $delim     A delimiter to go between the text and its prefix, for instance, a space. Defaults to
	 *                          an empty string
	 *
	 * @return string
	 */
	public function prefix($text, $prefix, $delim = '')
	{
		return $prefix . $delim . $text;
	}

	/**
	 * Appends a string onto another string
	 *
	 * @param string $text      The variable to be cast
	 * @param $suffix           Text to be atteched to $text variable
	 * @param string $delim     A delimiter to go between the text and its prefix, for instance, a space. Defaults to
	 *                          an empty string
	 *
	 * @return string
	 */
	public function suffix($text, $suffix, $delim = '')
	{
		return $text . $delim . $suffix;
	}

	/**
	 * Trims unwanted characters off a string
	 *
	 * @param string $text          The variable to be cast
	 * @param string | null $chars  Characters to be trimmed off string. If not set, only white space will be trimmed
	 *
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
	 * Trims unwanted characters off the end of a string
	 *
	 * @param string $text          The variable to be cast
	 * @param string | null $chars  Characters to be trimmed off string. If not set, only white space will be trimmed
	 *
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
	 * Trims unwanted characters off the start of a string
	 *
	 * @param string $text          The variable to be cast
	 * @param string | null $chars  Characters to be trimmed off string. If not set, only white space will be trimmed
	 *
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
	 * Capitalizes the first letter of every word in a string.
	 *
	 * @param $text                 The variable to be cast
	 * @param bool $maintainCase    If set to false, string will be converted to lowercase before the comment is run.
	 *
	 * @return string
	 */
	public function capitalize($text, $maintainCase = false)
	{
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
	 * Filter to replace strings
	 *
	 * @param mixeed $text  The variable to be cast
	 * @param $search       Value that will be replaced
	 * @param $replace      Replacement for $search
	 *
	 * @return string
	 */
	public function replace($text, $search, $replace)
	{
		return str_replace($search, $replace, $text);
	}

	/**
	 * Checks URL has correct protocol, with optional to replace existing e.g. http:// with https://
	 * Regex only checks for http, https, or ftp at the moment
	 *
	 * Called toUrl so as not to conflict with url rule
	 *
	 * @param string $url               The variable to be cast
	 * @param string $protocol          Protocol to append to URLs, defaults to 'http'. Adding '://' is not necessary
	 * @param bool $replaceExisting     If set to true, all protocols will be replaced with $protocol i.e. 'http'
	 *                                  could be replaced with 'https'
	 *
	 * @return string
	 */
	public function toUrl($url, $protocol = 'http', $replaceExisting = false)
	{
		$pattern = "~^(?:f|ht)tps?://~i";

		$protocol = str_replace('://', '', $protocol);

		if (!preg_match($pattern, $url)) {
			$url = $protocol . '://' . $url;
		}
		elseif ($replaceExisting) {
			$url = preg_replace($pattern, $protocol . '://', $url);
		}

		return $url;
	}

	/**
	 * Converts string to a slug, i.e. a lowercase string where non-url friendly characters are replaced with a
	 * hyphen
	 *
	 * Taken from http://sourcecookbook.com/en/recipes/8/function-to-slugify-strings-in-php
	 *
	 * @param $text     The variable to be cast
	 *
	 * @return string
	 */
	public function slug($text)
	{
		return TextFunction::toSlug($text, false);
	}
}