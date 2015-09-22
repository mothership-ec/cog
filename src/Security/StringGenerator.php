<?php

namespace Message\Cog\Security;

/**
 * Pseudorandom string generator.
 *
 * If the environment permits, the strings generated will by cryptographically
 * secure.
 *
 * Generated strings are in the format: [./0-9A-Za-z]{$length}
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StringGenerator
{
	const DEFAULT_LENGTH = 32;
	const CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';

	/**
	 * How many attempts the string generation methods will make to generate strings matching requirements. This is
	 * used both with match regular expresses (whose functionality is deprecated), and when filtering out characters
	 * not found in the whitelist
	 *
	 * @var int
	 */
	private $_tenacity = 1000;

	/**
	 * List of characters that are allowed to appear in the string. Defaults to all alphanumeric characters, as well
	 * as the dot '.' and slash '/'
	 *
	 * @var array
	 */
	private $_whitelist;

	/**
	 * Regex pattern that the generated string should match
	 *
	 * @deprecated  This property is deprecated, use the whitelist instead. See `setPattern()` docblock.
	 * @var string
	 */
	protected $_pattern;

	/**
	 * Split default characters into an array to set as whitelist
	 */
	public function __construct()
	{
		$this->_whitelist = str_split(self::CHARS);
	}

	/**
	 * @deprecated It is too inefficient and difficult to generate a string matching a regex, and on top of that these
	 *             methods only ever generate strings with alphanumeric characters, dots or slashes, and there is a set
	 *             length, so it's kind of dumb to try and match a regex where it's possible to have a regex that
	 *             couldn't possibly be created, i.e. if you had a regex of /^[a-z]{30}$/ but had set a length of 10,
	 *             it would never match.
	 *
	 * Allows setting a regex the String must match
	 * @param string $pattern the regex
	 *
	 * @return StringGenerator $this for chainability
	 */
	public function setPattern($pattern)
	{
		$this->_pattern = $pattern;

		return $this;
	}

	/**
	 * Set how hard the string generation method should try to create create the string matching the requirements.
	 *
	 * @param $tenacity
	 *
	 * @return StringGenerator
	 */
	public function setTenacity($tenacity)
	{
		if (!is_int($tenacity)) {
			throw new \InvalidArgumentException('Tenacity must be an integer');
		}

		$this->_tenacity = $tenacity;

		return $this;
	}

	/**
	 * Set which characters should be set in the whitelist
	 *
	 * @param $chars
	 *
	 * @return StringGenerator
	 */
	public function allowChars($chars)
	{
		$this->_whitelist = array_values($this->_getChars($chars));

		return $this;
	}

	/**
	 * Set which characters to exclude from the whitelist
	 *
	 * @param $chars
	 *
	 * @return StringGenerator
	 */
	public function disallowChars($chars)
	{
		$blacklist = $this->_getChars($chars);

		foreach ($this->_whitelist as $key => $char) {
			if (in_array($char, $blacklist, true)) {
				unset($this->_whitelist[$key]);
			}
		}

		$this->_whitelist = array_values($this->_whitelist);

		return $this;
	}

	/**
	 * Generates a pseudorandom string using the most preferred method.
	 *
	 * The order of preference is:
	 *  * Use the UNIX /dev/urandom file
	 *  * Use the UNIX /dev/random file
	 *  * Use the OpenSSL pseudo-random byte generator function
	 *  * Use PHP's built in `mt_rand()` function
	 *
	 * @param  integer $length Length of the string to generate
	 *
	 * @return string          Generated string
	 *
	 * @throws \UnexpectedValueException If no string was generated
	 */
	public function generate($length = self::DEFAULT_LENGTH)
	{
		$self   = $this;
		$string = null;

		if ($length < 1) {
			throw new \UnexpectedValueException('generate() expects an integer greater than or equal to 1');
		}

		$calls = [
			function() use ($self, $length) {
				return $self->generateFromUnixRandom($length, '/dev/arandom');
			},
			function() use ($self, $length) {
				return $self->generateFromUnixRandom($length, '/dev/urandom');
			},
			function() use ($self, $length) {
				return $self->generateFromUnixRandom($length, '/dev/random');
			},
			function() use ($self, $length) {
				return $self->generateFromOpenSSL($length);
			},
			function() use ($self, $length) {
				return $self->generateNatively($length);
			},
		];

		foreach ($calls as $call) {
			try {
				$string = $call();
				if ($string) {
					break;
				}
			}
			catch (\RuntimeException $e) {
				continue;
			}
			catch (Exception\GenerateStringException $e) {
				continue;
			}
		}

		if (!$string) {
			throw new \UnexpectedValueException('String could not be generated.');
		}

		return $string;
	}

	/**
	 * Generates a random, cryptographically secure string.
	 *
	 * This method uses /dev/urandom which is more random than mt_rand(), rand()
	 * etc as it uses atmospheric noise to generate its randomness.
	 *
	 * The output of /dev/urandom is binary so it's base64 encoded to make it
	 * more portable. Plus symbols are also replaced with periods to ensure the
	 * string will play well with algorithms such as bcrypt.
	 *
	 * @param  integer $length   Length of the string to generate
	 * @param  string  $path     Path to random noise generator
	 *
	 * @return string            Generated string
	 *
	 * @throws \RuntimeException If the random file does not exist or is unreadable
	 * @throws \RuntimeException If the random file returns an empty value
	 */
	public function generateFromUnixRandom($length = self::DEFAULT_LENGTH, $path = '/dev/urandom')
	{
		$callback = [$this, '_getUnixRandomString'];
		$parameters = [$path];

		return $this->_generateStringFromCallback($length, $callback, $parameters);
	}

	/**
	 * Generates a pseudorandom string via OpenSSL using the
	 * `openssl_random_pseudo_bytes` function.
	 *
	 * @param  integer $length   Length of the string to generate
	 *
	 * @return string            Generated string
	 *
	 * @throws \RuntimeException If the `openssl_random_pseudo_bytes` function
	 *                           does not exist.
	 */
	public function generateFromOpenSSL($length = self::DEFAULT_LENGTH)
	{
		$callback = [$this, '_getOpenSSLString'];

		return $this->_generateStringFromCallback($length, $callback);
	}

	/**
	 * Generate a pseudorandom string using the `mt_rand` function.
	 *
	 * This is the least preferable method for generating a pseudorandom string.
	 *
	 * @param  integer $length Length of the string to generate
	 *
	 * @return string          Generated string
	 */
	public function generateNatively($length = self::DEFAULT_LENGTH)
	{
		$callback = [$this, '_getNativeString'];

		return $this->_generateStringFromCallback($length, $callback);
	}

	/**
	 * Attempt to build a string using a callback. The number of attempts is based on the `$_tenacity` property.
	 * This method will generate strings, filter out characters that aren't in the whitelist, validate the length and
	 * then append a new string. If the number of characters is longer than the set length after the invalid characters
	 * have been filtered out, the string will then be trimmed down to size and returned.
	 *
	 * @param $length
	 * @param callable $callback
	 * @param array $parameters
	 *
	 * @return string
	 */
	private function _generateStringFromCallback($length, callable $callback, array $parameters = [])
	{
		if ($length < 1) {
			throw new \UnexpectedValueException('Cannot create string with length less than or equal to zero');
		}

		$parameters = array_merge([$length], $parameters);

		$rounds = 0;
		do {
			$loops = 0;
			$string = '';

			while (strlen($string) < $length && $loops < $this->_tenacity) {
				$newString = call_user_func_array($callback, $parameters);
				$newString = $this->_filterChars($newString);

				$string .= $newString;
				++$loops;
			}
			if (strlen($string) < $length) {
				throw new Exception\GenerateStringException('Could not generate string with provided whitelist: `' . implode('', $this->_whitelist) . '`');
			}

			$string = substr($string, 0, $length);
			++$rounds;

			if ($rounds >= $this->_tenacity) {
				throw new Exception\GenerateStringException('Could not generate string in ' . $this->_tenacity . ' attempts');
			}
		} while (!preg_match($this->_getPattern(), $string) && $rounds < $this->_tenacity);

		return $string;
	}

	/**
	 * Creates random string with no whitelist filtering
	 *
	 * Note: Your IDE might mark this method as unused, as it is called via call_user_func_array()
	 *
	 * @see generateFromUnixRandom()
	 * @param int $length
	 * @param string $path
	 *
	 * @return string
	 */
	private function _getUnixRandomString($length, $path)
	{
		if (!file_exists($path) || !is_readable($path)) {
			throw new \RuntimeException(sprintf('Unable to read `%s`.', $path));
		}

		$handle = fopen($path, 'rb');
		stream_set_read_buffer($handle, 0);
		$random = fread($handle, $length);
		fclose($handle);

		if (!$random) {
			throw new \RuntimeException(sprintf('`%s` returned an empty value.', $path));
		}

		$string = substr(base64_encode($random), 0, $length);
		$string = str_replace('+', '.', rtrim($string, '='));

		return $string;
	}

	/**
	 * Creates random string with no whitelist filtering
	 *
	 * Note: Your IDE might mark this method as unused, as it is called via call_user_func_array()
	 *
	 * @see generateFromOpenSSL()
	 * @param int $length
	 *
	 * @return string
	 */
	private function _getOpenSSLString($length)
	{
		if (!function_exists('openssl_random_pseudo_bytes')) {
			throw new \RuntimeException('Function `openssl_random_pseudo_bytes` does not exist.');
		}

		$random = openssl_random_pseudo_bytes($length);

		$string = substr(base64_encode($random), 0, $length);
		$string = str_replace('+', '.', rtrim($string, '='));

		return $string;
	}

	/**
	 * Select random characters from whitelist using mt_rand() and append to string. Return once it reaches correct
	 * length.
	 *
	 * Note: Your IDE might mark this method as unused, as it is called via call_user_func_array()
	 *
	 * @see generateFromNative()
	 * @param $length
	 *
	 * @return string
	 */
	private function _getNativeString($length)
	{
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= $this->_whitelist[mt_rand(0, (count($this->_whitelist) - 1))];
		}

		return $string;
	}

	/**
	 * Filter out characters from a string that are not found in the whitelist
	 *
	 * @param $string
	 *
	 * @return string
	 */
	private function _filterChars($string)
	{
		$chars = $this->_getChars($string);

		foreach ($chars as $key => $char) {
			if (!in_array($char, $this->_whitelist, true)) {
				unset($chars[$key]);
			}
		}

		return implode('', $chars);
	}

	/**
	 * Get the regex pattern to compare generated string with
	 *
	 * @return string
	 */
	private function _getPattern()
	{
		return $this->_pattern ?: '/.*/';
	}

	/**
	 * Either validate an array of individual characters, or take a string of characters and split it up into an array
	 *
	 * @param array | string $chars    List of characters to split
	 *
	 * @return array
	 */
	private function _getChars($chars)
	{
		if (!is_string($chars) && !is_array($chars)) {
			throw new \InvalidArgumentException('Allowed characters must be a string or an array');
		}

		if (is_string($chars)) {
			$chars = str_split($chars);
		}

		foreach ($chars as $char) {
			if (!is_string($char) || strlen($char) !== 1) {
				throw new \LogicException('Each value in character list must be a one character string');
			}
		}

		return $chars;
	}
}
