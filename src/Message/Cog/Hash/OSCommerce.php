<?php

namespace Message\Cog\Hash;

use \InvalidArgumentException;

/**
 * Hash class for OSCommerce's default encryption algorithm.
 *
 * @author Joe Holdcroft <joe@message.uk.com>
 */
class OSCommerce extends Hash
{
	/**
	 * Encrypts a string using the default OSCommerce encryption algorithm.
	 *
	 * @param 	string $string 	The string to be encrypted
	 * @param 	string $salt 	Optional salt to be used when encrypting
	 *
	 * @return 	string 			Encrypted hash
	 */
	final public function encrypt($string, $salt = null)
	{
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
		// CHECK HASH CONTAINS SALT AND SEPARATOR
		if (strpos($hash, ':') === false) {
			throw new InvalidArgumentException('Invalid hash passed to ' . __METHOD__);
		}
		// GET SALT FROM HASH
		list($rawHash, $salt) = explode(':', $hash);

		// RE-HASH THE PASSWORD AND CHECK AGAINST INPUT HASH
		return ($hash === $this->encrypt($string, $salt));
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