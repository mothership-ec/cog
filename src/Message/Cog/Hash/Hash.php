<?php

namespace Message\Cog\Hash;

use \InvalidArgumentException;

/**
 * Base hash class, used for encrypting and decrypting strings.
 *
 * This class is extended by subclasses for each algorithm supported by the
 * platform.
 *
 * @author Joe Holdcroft <joe@message.uk.com>
 * @abstract
 */
abstract class Hash
{
	/**
	 * Returns the relevant subclass of this class using the input algorithm.
	 *
	 * @param string $algorithm Name of subclass to retrieve
	 *
	 * @return Hash 			Instance of Hash subclass for this algorithm
	 */
	final public static function create($algorithm)
	{
		$className = __CLASS__ . '\\' . $algorithm;
		if (!class_exists($className)) {
			throw new InvalidArgumentException('Hash algorithm not defined: ' . $algorithm);
		}

		return new $className;
	}

	/**
	 * Encrypt a plain text string, with an optional salt.
	 *
	 * @param 	string	$string	Plain text string to hash
	 * @param 	boolean	$salt	Optional salt to use
	 *
	 * @return 	string 			Encrypted hash
	 */
	abstract public function encrypt($string, $salt = null);

	/**
	 * Check if a plain text string matches a hash.
	 *
	 * @param 	string	$string	Plain text string
	 * @param 	string	$hash	Full hashed string
	 *
	 * @return 	boolean 		Result of match check
	 */
	abstract public function check($string, $hash);

	/**
	 * Generates a random, cryptographically secure salt
	 *
	 * This method uses /dev/urandom which is more random than mt_rand(), rand()
	 * etc. as it uses atmospheric noise to generate it's randomness.
	 *
	 * Salts are in the format [./0-9A-Za-z]{$length}.
	 *
	 * @param  integer $length Length of the salt to generate.
	 * @param  string  $path   Path to random noise generator
	 *
	 * @return string          Generated salt.
	 */
	public function generateSalt($length = 32, $path = '/dev/urandom')
	{
		if (!is_readable($path)) {
			throw new \RuntimeException(sprintf('Unable to read `%s`.', $path));
		}

		// /dev/urandom appears as a file which we can read.
		$handle = fopen($path, 'r');
		$urandom = fread($handle, $length);
		fclose($handle);

		if (!$urandom) {
			throw new \RuntimeException(sprintf('`%s` returned an empty value.', $path));
		}

		// The output of /dev/urandom is binary so it's base64'ed to make it
		// more portable. Also pluses are replaced with periods to ensure it
		// plays well with bcrypt.
		$salt = substr(base64_encode($urandom), 0, $length);
		$salt = str_replace('+', '.', $salt);

		return $salt;
	}
}