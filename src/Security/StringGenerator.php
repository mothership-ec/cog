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
	protected $_pattern = '/.*/';


	/**
	 * Allows setting a regex the String must match
	 * @param string $pattern the regex
	 * @return StringGenerator $this for chainability
	 */
	public function setPattern($pattern)
	{
		$this->_pattern = $pattern;
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
		$self  = $this;
		if ($length < 1) {
			throw new \UnexpectedValueException('generate() expects an integer greater than or equal to 1');
		}
		$calls = array(
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
		);

		foreach ($calls as $call) {
			try {
				$string = $call();
				if ($string) {
					break;
				}
			}
			catch (\Exception $e) {
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
		if (!file_exists($path) || !is_readable($path)) {
			throw new \RuntimeException(sprintf('Unable to read `%s`.', $path));
		}
		if ($length < 1) {
			throw new \UnexpectedValueException('generate() expects an integer greater than or equal to 1');
		}

		$rounds = 0;
		do {
			$handle = fopen($path, 'rb');
			stream_set_read_buffer($handle, 0);
			$random = fread($handle, $length);
			fclose($handle);

			if (!$random) {
				throw new \RuntimeException(sprintf('`%s` returned an empty value.', $path));
			}

			$string = substr(base64_encode($random), 0, $length);
			$string = str_replace('+', '.', rtrim($string, '='));
			
			++$rounds;
		} while (!preg_match($this->_pattern, $string) && $rounds < $length);

		return $string;
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
		if (!function_exists('openssl_random_pseudo_bytes')) {
			throw new \RuntimeException('Function `openssl_random_pseudo_bytes` does not exist.');
		}

		do {
			$random = openssl_random_pseudo_bytes($length);

			$string = substr(base64_encode($random), 0, $length);
			$string = str_replace('+', '.', rtrim($string, '='));
		} while (!preg_match($this->_pattern, $string));
		

		return $string;
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
		$chars      = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
		$charLength = strlen($chars) - 1;

		do {
			$string     = '';

			for ($i = 0; $i < $length; $i++) {
				$string .= $chars[mt_rand(0, $charLength)];
			}
		} while (!preg_match($this->_pattern, $string));


		return $string;
	}
}
