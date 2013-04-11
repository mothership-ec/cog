# Validation

The validation component makes it easy to ensure that an associative array matches a known, valid format. It also makes it possible to modify the data to ensure it passes validation (e.g adding `http://` before all URLs).

## Getting started

Let's say we want to validate the following array of data which could have come from a user-submitted form.

    $data = array(
        'first_name'    => 'Bobby',
        'last_name'     => 'Jones',
        'email_address' => 'b.jones@example.org',
        'age'           => '24',
    );

Firstly we create and instance of the validation object:

    $validator = new \Message\Cog\Validation\Validator;
    
Next we go about adding rules. In the validator component rules are associated to a field. A field is basically an expected key in the data array.

A field can have multiple rules and each rule defines how the data in that field should look. For a field to validate it must conform to every rule which has been declared.  For the entire array to validate each field must be valid.

Fields and rules are declared using a fluent interface:

    $validator
	    ->field('first_name') // Add a required field to the validator.
			->alnum() // must be alpha numeric
			->length(3, 10) // between 3 and 10 characters
		->field('last_name')
		    ->length(0, 25) // between 0 and 25 characters
		->field('email_address') 
		    ->email() // ensures the field is a valid email address
		->field('age')
		    ->min(18) // No under 18's allowed!   
    ;
    
Once fields and rules have been declared you run the `validate()` method and pass in the data we want to run our rules against.

    if ($validator->validate($data)) { //returns true or false
        addUserToDB($validator->getData);
    }
    
In this example the `validate()` will return `true` and a user will be added to the database. But what if we pass in invalid data?

This time let's pass in the following data and output any error messages:

    $data = array(
        'first_name'    => 'S.',
        'last_name'     => 'Williamson-McFitzherbert',
        'email_address' => 'swm_example.org',
        'age'           => '16',
    );
    
    $validator->validate($data); // returns false
    $errors = $validator->getMessages();
    var_dump($errors);
    
`getMessages()` returns an array containing error messages which have been raised. The output of `var_dump` will look like this:

	array(3) {
	  'first_name' =>
	  array(2) {
	    [0] =>
	    string(36) "First Name must be alphanumeric."
	    [1] =>
	    string(47) "First Name must be between 3 and 10 characters."
	  }
	  'email_address' =>
	  array(1) {
	    [0] =>
	    string(43) "Email Address must be a valid email address"
	  }
	  'age' =>
	  array(1) {
	    [0] =>
	    string(40) "Age must be equal to or greater than 18."
	  }
	}
	
The `Validator` class is able to determine the field name and generate nice error messages just from the rules you define. In exactly 83% of cases this should be enough but sometimes you might want to set your own field names and error messages:

    $validator
	    ->field('first_name', 'Forename')
			->alnum()
			->length(3, 10)
		    ->error('%s must be below 11 and above 2 characters.')
		->field('last_name', 'Surname')
		    ->length(0, 15)
		->field('email_address') 
		    ->email()
		->field('age')
		    ->min(18)
	        ->error('You must be over 18 to signup')
    ;
    
By declaring a field you automatically make it a required element. If the field is left blank then the validation will fail. To make a field optional call the `optional()` method. If the field is blank then the rules won't be applied and the field will be skipped when validating.

## Filters

Filters modify the data passed to `validate()`. They can be applied before or after rules are validated.