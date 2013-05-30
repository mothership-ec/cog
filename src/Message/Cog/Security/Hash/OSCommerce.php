<?php

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\Salt;

use \InvalidArgumentException;

/**
 * Hash class for OSCommerce's default encryption algorithm.
 *
 * @author Joe Holdcroft <joe@message.uk.com>
 */
class OSCommerce extends Hash
{
<<<<<<< HEAD:src/Message/Cog/Security/Hash/OSCommerce.php
	const SALT_SEPARATOR = ':';

	protected $saltGenerator;

	/**
	 * __construct()
	 *
	 * @param Salt $saltGenerator The pseudorandom string generator class
	 */
	public function __construct($saltGenerator)
	{
		$this->_saltGenerator = $saltGenerator;
	}

	/**
	 * Hash a string using the OSCommerce hashing algorithm.
	 *
	 * If `$salt` is passed as null, the salt is automatically generated using
	 * the same functionality found in OSCommerce's hashing functionality rather
	 * than `generateSalt()` available in the base class.
=======
	/**
	 * Encrypts a string using the default OSCommerce encryption algorithm.
>>>>>>> refs/heads/master:src/Message/Cog/Hash/OSCommerce.php
	 *
	 * @param 	string $string 	The string to be encrypted
	 * @param 	string $salt 	Optional salt to be used when encrypting
	 *
	 * @return 	string 			Encrypted hash
	 */
	final public function encrypt($string, $salt = null)
	{
<<<<<<< HEAD:src/Message/Cog/Security/Hash/OSCommerce.php
		// Generate a salt a-la OSCommerce
=======
>>>>>>> refs/heads/master:src/Message/Cog/Hash/OSCommerce.php
		if (is_null($salt)) {
			// GENERATE A RANDOM SALT
			for ($i = 0; $i < 10; $i++) {
				$salt .= $this->_tepRand();
			}
			// MD5 IT AND GET GET THE FIRST 2 CHARACTERS
			$salt = substr(md5($salt), 0, 2);
		}
		// CREATE HASH USING PASSWORD AND SALT
		$hash = md5($salt . $string) . ':' . $salt;

		return $hash;
	}


	/**
	 * Checks if a plaintext string matches an encrypted string using the
	 * default OSCommerce algorithm.
	 *
	 * @param 	string $string 	Plain text string
	 * @param 	string $hash 	Encrypted string
	 *
	 * @return 	boolean 		Result of the check
	 */
	final public function check($string, $hash)
	{
<<<<<<< HEAD:src/Message/Cog/Security/Hash/OSCommerce.php
		if (false === strpos($hash, self::SALT_SEPARATOR)) {
			throw new \InvalidArgumentException(sprintf('Hash `%s` is invalid: it does not contain a salt.', $hash));
=======
		// CHECK HASH CONTAINS SALT AND SEPARATOR
		if (strpos($hash, ':') === false) {
			throw new InvalidArgumentException('Invalid hash passed to ' . __METHOD__);
>>>>>>> refs/heads/master:src/Message/Cog/Hash/OSCommerce.php
		}
		// GET SALT FROM HASH
		list($rawHash, $salt) = explode(':', $hash);

<<<<<<< HEAD:src/Message/Cog/Security/Hash/OSCommerce.php
		list($plainHash, $salt) = explode(self::SALT_SEPARATOR, $hash, 2);

		return $hash === $this->encrypt($string, $salt);
=======
		// RE-HASH THE PASSWORD AND CHECK AGAINST INPUT HASH
		return ($hash === $this->encrypt($string, $salt));
>>>>>>> refs/heads/master:src/Message/Cog/Hash/OSCommerce.php
	}

	/**
	 * Random integer generator taken from OSCommerce.
	 *
	 * This is copied from OSCommerce's tep_rand() function. The function
	 * definition is found in OSCommerce's filesystem here:
	 * /catalog/admin/includes/functions/general.php
	 *
	 * @return 	int 		The randomly generated integer
	 */
	final protected function _tepRand()
	{
		static $seeded;

		if (!$seeded) {
			mt_srand((double) microtime() * 1000000);
			$seeded = true;
		}

		return mt_rand();
	}
}