<?php

namespace Message\Cog\Security\Hash;

use Message\Cog\Security\StringGenerator;

/**
 * A Bcrypt implementation for the hashing component.
 *
 * One of the most secure hash algorithms around at the moment. Much slower than
 * md5 and sha1, it makes brute force attacks difficult. Additionally, as CPUs
 * get faster you can increase the `self::WORK_FACTOR` constant to keep bcrypt
 * hashing at the same speed.
 *
 * @author James Moss <james@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Bcrypt implements HashInterface
{
	const WORK_FACTOR = 8; // Value between 4 and 31

	protected $_saltGenerator;

	/**
	 * Constructor.
	 *
	 * @param StringGenerator $saltGenerator The pseudorandom string generator class
	 */
	public function __construct(StringGenerator $saltGenerator)
	{
		$this->_saltGenerator = $saltGenerator;
	}

	/**
	 * Hash a string using bcrypt.
	 *
	 * The salt passed must be 22 bytes or more. Only the first 22 bytes will be
	 * used as the salt.
	 *
	 * Generates *0 if invalid characters are used. Exception thrown if
  	 * *0 is returned.
	 *
	 * @param  string      $string String to hash
	 * @param  string|null $salt   Salt to use
	 *
	 * @return string              The hashed value
	 *
	 * @throws \InvalidArgumentException If the salt is less than 22 bytes long
	 * @throws \InvalidArgumentException If the sale contains invalid characters
	 */
	public function encrypt($string, $salt = null)
	{
		if (is_null($salt)) {
			$salt = $this->_saltGenerator->generate(22);
		}

		if (strlen($salt) < 22) {
			throw new \InvalidArgumentException(sprintf(
				'Salt `%s` must be at least 22 bytes when using Bcrypt.',
				$salt
			));
		}

		// Using a salt formatted in this way tells crypt() to use bcrypt
		$bcryptSalt = '$2a$' . str_pad(self::WORK_FACTOR, 2, '0', STR_PAD_LEFT) . '$'
					 . substr($salt, 0, 22);

		$crypto = crypt($string, $bcryptSalt);

		if ('*0' === $crypto) {
			throw new \InvalidArgumentException(sprintf(
				'Salt `%s` contains invalid characters.',
				$salt
			));
		}

		return $crypto;
	}

	/**
	 * Check if a string matches a bcrypt hash using a timing attack resistant approach
	 *
	 * @param  string $string Plain text string to check
	 * @param  string $hash   Full bcrypt hashed string
	 *
	 * @return boolean        Result of match check
	 */
	public function check($string, $hash)
	{
            if (!function_exists('crypt')) {
                trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
                return false;
            }
            $ret = crypt($string, $hash);
            if (!is_string($ret) || $this->_strlen($ret) != $this->_strlen($hash) || $this->_strlen($ret) <= 13) {
                return false;
            }

            $status = 0;
            for ($i = 0; $i < $this->_strlen($ret); $i++) {
                $status |= (ord($ret[$i]) ^ ord($hash[$i]));
            }

            return $status === 0;
	}
	
        /**
         * Count the number of bytes in a string
         *
         * We cannot simply use strlen() for this, because it might be overwritten by the mbstring extension.
         * In this case, strlen() will count the number of *characters* based on the internal encoding. A
         * sequence of bytes might be regarded as a single multibyte character.
         *
         * @param string $binary_string The input string
         *
         * @internal
         * @return int The number of bytes
         */
        protected function _strlen($binary_string) {
            if (function_exists('mb_strlen')) {
                return mb_strlen($binary_string, '8bit');
            }
            return strlen($binary_string);
        }
        
        /**
         * Get a substring based on byte limits
         *
         * @see _strlen()
         *
         * @param string $binary_string The input string
         * @param int    $start
         * @param int    $length
         *
         * @internal
         * @return string The substring
         */
        protected function _substr($binary_string, $start, $length) {
            if (function_exists('mb_substr')) {
                return mb_substr($binary_string, $start, $length, '8bit');
            }
            return substr($binary_string, $start, $length);
        }
}
