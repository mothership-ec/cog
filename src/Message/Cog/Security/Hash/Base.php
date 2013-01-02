<?php

namespace Message\Cog\Security\Hash;

/**
 * Base hash class, contains helpful methods for the hashing algorithms.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class Base
{
	const URANDOM_PATH = '/dev/urandom';

	/**
	 * Generates a random, cryptographically secure salt.
	 *
	 * This method uses /dev/urandom which is more random than mt_rand(), rand()
	 * etc as it uses atmospheric noise to generate its randomness.
	 *
	 * The output of /dev/urandom is binary so it's base64 encoded to make it
	 * more portable. Plus symbols are also replaced with periods to ensure the
	 * salt will play well with algorithms such as bcrypt.
	 *
	 * Salts are in the format [./0-9A-Za-z]{$length}
	 *
	 * @param  integer $length Length of the salt to generate
	 * @param  string  $path   Path to random noise generator
	 *
	 * @return string          Generated salt
	 *
	 * @throws \RuntimeException If /dev/urandom is unreadable
	 * @throws \RuntimeException If /dev/urandom returns an empty value
	 */
	public function generateSalt($length = 32)
	{
		if (!is_readable(self::URANDOM_PATH)) {
			throw new \RuntimeException(sprintf('Unable to read `%s`.', self::URANDOM_PATH));
		}

		$handle  = fopen(self::URANDOM_PATH, 'r');
		$urandom = fread($handle, $length);
		fclose($handle);

		if (!$urandom) {
			throw new \RuntimeException(sprintf('`%s` returned an empty value.', self::URANDOM_PATH));
		}

		$salt = substr(base64_encode($urandom), 0, $length);
		$salt = str_replace('+', '.', $salt);

		return $salt;
	}
}