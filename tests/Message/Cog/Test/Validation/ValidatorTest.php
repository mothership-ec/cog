<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	public function testNothing()
	{
		$validator = new Validator;

		$validator
			->field('last_name') 
			->field('first_name') // Add a required field to the validator.
				->optional() // This makes it optional if the field is empt
				->notAlnum() // must be alpha numeric
				->length(3, 15) // between 3 and 15 characters
				->capitalize() // capitalise each word before validation runs
				->trimAfter() // trim the field after its been validated
			->field('email', 'Email Address') // second parameter is the human readable field name, when ommited the human readable name is generated from the field name.
				->email() // field must be in the format of an email
		;

		$validator->validate(array(
			'first_name' => 'assd64add]asd',
			'email'	=> 'asda@sd.com',
		));

		$this->assertTrue(true);
	}
}