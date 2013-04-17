# Validation

The validation component makes it easy to ensure that an associative array matches a known, valid format. It also makes
it possible to modify the data to ensure it passes validation (e.g adding `http://` before all URLs).

## Getting started

Let's say we want to validate the following array of data which could have come from a user-submitted form.

    $data = array(
        'first_name'    => 'Bobby',
        'last_name'     => 'Jones',
        'email_address' => 'b.jones@example.org',
        'age'           => '24',
    );

Firstly we create an instance of the validation object:

    $validator = new \Message\Cog\Validation\Validator;
    
Next we go about adding rules. In the validator component rules are associated to a field. A field is basically an
expected key in the data array.

A field can have multiple rules and each rule defines how the data in that field should look. For a field to validate
it must conform to every rule which has been declared. For the entire array to validate each field must be valid.

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
    
Once fields and rules have been declared you run the `validate()` method and pass in the data we want to run our rules
against.

    if ($validator->validate($data)) { //returns true or false
        addUserToDB($validator->getData);
    }
    
In this example the `validate()` will return `true` and a user will be added to the database. But what if we pass in
invalid data?

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
    
`getMessages()` returns an array containing error messages which have been raised. The output of `var_dump` will look
like this:

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
	
The `Validator` class is able to determine the field name and generate nice error messages just from the rules you
define. In exactly 83% of cases this should be enough but sometimes you might want to set your own field names and
error messages:

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
	        ->error('You must be over 18 to sign up')
        ->field('address')
            ->optional()
    ;

Here, the custom field name is the second parameter of the `field()` method, i.e. `Forename` in the `first_name` field.
The error message is defined using the `error()` method.
    
By declaring a field you automatically make it a required element. If the field is left blank then the validation will
fail. To make a field optional call the `optional()` method, as shown on the `address` field above. If the field is
blank then the rules won't be applied and the field will be skipped when validating.

## Filters

Filters modify the data passed to `validate()`. They can be applied before or after rules are validated.
This is determined by adding `Before` or `After` to the method call, for instance:

	$validator
		->field('url')
			->toUrlBefore()
			->url()
	;

In this example, if you were to validate the url `message.co.uk`, the toUrl() filter would be called
before the url() rule, ensuring that the http:// protocol is prefixed to the string, making it
`http://message.co.uk`. It will then pass the url() validation rule.

If you were to change this to:

	$validator
		->field('url')
			->toUrlAfter()
			->url()
	;

Passing `message.co.uk` to the validator would cause validation to fail and return false, as the filter is run after
the rule.

## Rules

Where filters modify the data passed to `validate()`, rules determine what is required for data to pass validation.
In the example below, data will not pass validation is `age` doesn't fall between 30 and 40:

	$validator
		->field('age')
			->between(30, 40)
	;

Rules can be inverted by prepending the method name with `not` (and camel casing it). So we can invert the example
above so that data will not pass validation if `age` falls between 30 and 40:

	$validator
		->field(`age`)
			->notBetween(30, 40)
	;

## `Other` Filters and Rules

The Filter\Other and Rule\Other classes allow users to create custom filters and validators without having to edit
the component itself, i.e. from native PHP functions. You can do this using the 'filter()' and 'rule()' methods. For
instance, if you wanted to create an md5 hash of the input before submitting it, and ensure it is a string (although a
rule already exists for this), you could do the following:

	$validator
		->field('password')
			->filter('md5')
			->rule('is_string')
	;

## Adding Filters and Rules to the Component

To add new filters and rules to the component, you need to create classes that implement the CollectionInterface,
and add a `register()` method. The `register()` method must have a Loader parameter. This is where you will register
your filters/rules.

Each rule or filter is essentially a method within a CollectionInterface. A rule must always return a boolean, but a
filter can return any type of data. In all cases, the first parameter of the method is the data that is passed to it
to be validated. When calling the method in the fluent interface, this first parameter is assumed and therefore is
not stated, for instance the following method:

	public function example($var)
	{
		return true;
	}

would be called by the validator as so:

	$validator
		->field('field')
			->example()
	;

If there are extra parameters, these are shifted over when called by the validator, so this:

	public function example($var, $secondParam)
	{
		return true;
	}

becomes this when called by the validator:

	$validator
		->field('field')
			->example('valueForSecondParam')
	;

Once you have created your methods, you need to assign these as either rules or filters in the `register()` method
using the Loader's `registerRule()` and `registerFilter()` methods:

	// The first parameter is the name of the method being called.
	// The second parameter is a callable array, so the first value is an object that contains the method, i.e. $this
	//      and the second value is the name of the method.
	// The third parameter is the error message that will show up when the data fails validation. It uses sprintf() so
	//      the first %s represents the value, and the second represents whether 'not' has been set.

	$loader->registerRule('example', array($this, 'example'), '%s must %s follow the example rule.')

	// The first two parameters match those of `registerRule()`, but as it is a filter, no error message is necessary.

	$loader->registerFilter('example', array($this, 'example'));

## Limitations

When creating filters and rules, due to the fluent interface, it is worth bearing in mind that you cannot register
any two filters or rules that have the same name as this will cause a conflict. An example of this being worked around
is the toUrl() filter. If this had been called url(), there would be no way for the validator to determine whether
this is referring to the filter or the rule.