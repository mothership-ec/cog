<?php

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\Salt;

/**
 * MD5 implementation of the hash interface with an appended salt.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 */
class MD5 implements HashInterface
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
	 * Hash a string using MD5, with an optional encryption salt.
	 *
	 * @param  string      $string String to be exposed to the MD5 hashing process
	 * @param  string|null $salt
	 *
	 * @return string      Hashed value
	 */
	public function encrypt($string, $salt = null)
	{
		if (is_null($salt)) {
			$salt = $this->_saltGenerator->generate();
		}

		return md5($string . self::SALT_SEPARATOR . $salt) . self::SALT_SEPARATOR . $salt;
	}

	/**
	 * Check if encrypted string matches MD5 hash
	 *
	 * Detects a separator value (`self::SALT_SEPARATOR`) and extracts the salt
	 * if set. The salt is then used to compare against the string.
	 *
	 * @param  string $string String to be exposed to the checking
	 * @param  string $hash   MD5 hashed string
	 *
	 * @return boolean        Result of check
	 *
	 * @throws \InvalidArgumentException If the hash does not contain a salt
	 */
	public function check($string, $hash)
	{
		if (false === strpos($hash, self::SALT_SEPARATOR)) {
			throw new \InvalidArgumentException(sprintf('Hash `%s` is invalid: it does not contain a salt.', $hash));
		}

		// Creates new array out of hashed components
		list($plainHash, $salt) = explode(self::SALT_SEPARATOR, $hash, 2);

		return ($hash === $this->encrypt($string, $salt));
	}
}