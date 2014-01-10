<?php

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\StringGenerator;

/**
 * An implementation of the hashing component for OSCommerce's custom hashing
 * algorithm using md5 and randomised salts.
 *
 * This hashing functionality should only be used when working with ported
 * hashes (e.g. user passwords) from an OSCommerce database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class OSCommerce implements HashInterface
{
	const SALT_SEPARATOR = ':';

	protected $saltGenerator;

	/**
	 * Constructor.
	 *
	 * @param Salt $saltGenerator The pseudorandom string generator class
	 */
	public function __construct(StringGenerator $saltGenerator)
	{
		$this->_saltGenerator = $saltGenerator;
	}

	/**
	 * Hash a string using the OSCommerce hashing algorithm.
	 *
	 * If `$salt` is passed as null, the salt is automatically generated using
	 * the same functionality found in OSCommerce's hashing functionality rather
	 * than `generateSalt()` available in the base class.
	 *
	 * @param string      $string The string to be encrypted
	 * @param string|null $salt   Salt to be used when encrypting. If left null,
	 *                            this is automatically generated
	 *
	 * @return string             The hashed value
	 */
	public function encrypt($string, $salt = null)
	{
		// Generate a salt a-la OSCommerce
		if (is_null($salt)) {
			for ($i = 0; $i < 10; $i++) {
				$salt .= $this->_tepRand();
			}
			$salt = substr(md5($salt), 0, 2);
		}

		// Generate the hash
		$hash = md5($salt . $string) . self::SALT_SEPARATOR . $salt;

		return $hash;
	}


	/**
	 * Check if a string matches an OSCommerce hashed string.
	 *
	 * @param  string $string String to check
	 * @param  string $hash   Full OSCommerce hashed string
	 *
	 * @return boolean        Result of match check
	 *
	 * @throws \InvalidArgumentException If the hash does not contain a salt
	 */
	public function check($string, $hash)
	{
		if (false === strpos($hash, self::SALT_SEPARATOR)) {
			throw new \InvalidArgumentException(sprintf('Hash `%s` is invalid: it does not contain a salt.', $hash));
		}

		list($plainHash, $salt) = explode(self::SALT_SEPARATOR, $hash, 2);

		return $hash === $this->encrypt($string, $salt);
	}

	/**
	 * Random integer generator taken from OSCommerce.
	 *
	 * This is copied from OSCommerce's tep_rand() function. The function
	 * definition is found in OSCommerce's filesystem here:
	 * `/catalog/admin/includes/functions/general.php`
	 *
	 * @return int The randomly generated integer
	 */
	protected function _tepRand()
	{
		static $seeded;

		if (!$seeded) {
			mt_srand((double) microtime() * 1000000);
			$seeded = true;
		}

		return mt_rand();
	}
}