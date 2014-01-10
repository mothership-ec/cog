<?php

namespace Message\Cog\Test\Security\Hash;

/**
 * A static container for data providers for the Security\Hash classes.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 */
class DataProvider
{
	/**
	 * Get strings that should be successfully hashed.
	 *
	 * @return array Array of data provider values
	 */
	public static function getStrings()
	{
		return array(
			array('asimplepassword'), 		// String
			array('aSimplePassword'), 		// String with cases
			array('a simple password 123'), // String with integer and spaces
			array('a simple password'), 	// String with spaces
			array(''), 						// Blank password
			array('12345678'), 				// Integers only
			array('!@£$%^&*()_+=-'), 		// Special characters
			array('short'), 				// Short password
			array('password!@£$%^&*()'),	// String with special characters
			array('password123!@£$'),		// String with integer and special characters
			array('password 123 !@£$'),  	// String with integer, special characters and spaces
			array('areallylongpassword1234')// Long password with string and integer
		);
	}
}