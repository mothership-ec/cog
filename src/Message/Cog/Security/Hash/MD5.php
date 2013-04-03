<?php 

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\Salt;

/**
 * MD5 implementation of the hash interface.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 */
class MD5 implements HashInterface
{
	const SALT_SEPARATOR = ':';

	protected $_saltGenerator;

	/**
	* __construct
	*
	* @param Salt $saltGenerator - Instantiation of psuedorandom string generator class
	*/
	public function __construct(Salt $saltGenerator)
	{
		$this->_saltGenerator = $saltGenerator;
	}

	/**
	 * Hash a string implementing md5, with optional encryption salt
	 *
	 * @param  string 		$string String to be exposed to the md5 hashing process
	 * @param  string|null 	$salt
	 *
	 * @return string 		Hashed value
	 */
	public function encrypt($string, $salt = null)
	{
		if ($salt) {
			$salt = $this->_saltGenerator->generate();
		}

		return md5($string . self::SALT_SEPARATOR . $salt) . self::SALT_SEPARATOR . $salt;
	}

	/**
	 * Check if encrypted string matches md5 value
	 *
	 * Detects for a separator value (self::SALT_SEPARATOR) and extracts a salt if set.
	 * Salt is then used to compare against the string.
	 * 
	 * @param  string $string 	 String to be exposed to the checking
	 * @param  string $hash 	 md5 hashed string
	 *
	 * @return boolean 		     Result of check 
	 */
	public function check($string, $hash)
	{
		$salt = null;

		// Locates salt then extracts it
		if (false !== strpos($hash, self::SALT_SEPARATOR)) {

			// Creates new array out of hashed components
			$array = explode(SELF::SALT_SEPARATOR, $hash);

			// Sets popped array as $salt
			$salt = array_pop($array);
		}

		return ($hash === $this->encrypt($string, $salt));
	}
}