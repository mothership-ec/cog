<?php

namespace Message\Cog\Security\Hash;

/**
 * Interface for all Hash classes, which are responsible for hashing strings
 * and comparing hashes to strings.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface HashInterface
{
	/**
	 * Hash a string, with an optional salt.
	 *
	 * @param  string      $string String to hash
	 * @param  string|null $salt   Optional salt to use
	 *
	 * @return string              The hashed value
	 */
	public function encrypt($string, $salt = null);

	/**
	 * Check if a string matches a hash.
	 *
	 * @param  string $string String to check
	 * @param  string $hash   Full hashed string
	 *
	 * @return boolean        Result of match check
	 */
	public function check($string, $hash);
}