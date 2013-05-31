<?php

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\Salt;

/**
 * SHA1 implementation of the hash interface with an appended salt.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SHA1 implements HashInterface
{
	const SALT_SEPARATOR = ':';

	protected $_saltGenerator;

	/**
	 * Constructor.
	 *
	 * @param Salt $saltGenerator The pseudorandom string generator class
	 */
	public function __construct(Salt $saltGenerator)
	{
		$this->_saltGenerator = $saltGenerator;
	}

	/**
	 * Hash a string using SHA1, with an optional salt.
	 *
	 * If a salt is provided, it is appended to the string before hashing and
	 * also after the hash (for identification) using the separator value set
	 * as `self::SALT_SEPARATOR`.
	 *
	 * @param  string      $string String to hash
	 * @param  string|null $salt   Optional salt to use
	 *
	 * @return string              The hashed value
	 */
	public function encrypt($string, $salt = null)
	{
		if (is_null($salt)) {
			$salt = $this->_saltGenerator->generate();
		}

		return sha1($string . self::SALT_SEPARATOR . $salt) . self::SALT_SEPARATOR . $salt;
	}

	/**
	 * Check if a string matches a SHA1 hash.
	 *
	 * Detects presence of the separator value set as `self::SALT_SEPARATOR` and
	 * finds the salt, if set, to use to compare the string. Throws exception if
	 * no salt is given.
	 *
	 * @param  string $string String to check
	 * @param  string $hash   Full SHA1 hashed string
	 *
	 * @return boolean        Result of match check
	 *
	 * @throws \InvalidArgumentException If the hash does not contain a salt
	 */
	public function check($string, $hash)
	{
		$salt = null;

		if (false === strpos($hash, self::SALT_SEPARATOR)) {
			throw new \InvalidArgumentException(sprintf('Hash `%s` is invalid: it does not contain a salt.', $hash));
		}

		// Look for a salt, extract it
		list($plainHash, $salt) = explode(self::SALT_SEPARATOR, $hash, 2);

		return ($hash === $this->encrypt($string, $salt));
	}
}