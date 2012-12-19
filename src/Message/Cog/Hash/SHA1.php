<?php

namespace Message\Cog\Hash;

use \InvalidArgumentException;

class SHA1 extends Hash
{
	final public function encrypt($string, $salt = null)
	{
		return sha1($string);
	}

	final public function check($string, $hash)
	{
		return ($hash === $this->encrypt($string));
	}
}