<?php

namespace Message\Cog\Hash;

/**
 * A Bcrypt implementation for the hashing component.
 *
 * One of the most secure hash algorithms around at the moment. Much slower than
 * md5 and sha1, it makes brute force attacks difficult. Additionally, as CPUs
 * get faster you can increase the WORK_FACTOR constant to keep bcrypt
 * hashing at the same speed.
 */
class Bcrypt extends Hash
{
	const WORK_FACTOR = 8; // Value between 4 and 31

	public function encrypt($password, $salt = null)
	{
		if(strlen($salt) < 22) {
			throw new \InvalidArgumentException('Salt for bcrypt must be at least 22 bytes.');
		}

		// Using a salt formatted in this way tells crypt() to use bcrypt.
		$bcrypt_salt =
			'$2a$' . str_pad(self::WORK_FACTOR, 2, '0', STR_PAD_LEFT) . '$' .
			substr($salt, 0, 22)
		;

		return crypt($password, $bcrypt_salt);
	}

	public function check($password, $hash)
	{
		return crypt($password, $hash) === $hash;
	}
}
