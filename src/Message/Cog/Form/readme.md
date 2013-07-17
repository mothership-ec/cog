# Form

The form integrates the Symfony Form component with the rest of the framework, specifically templating and validation.

Due to the complexity of the Symfony Form component, and the fact that many of the classes have several private properties and methods, the majority of the classes are not extended. As a result, we must use a handler to use the Form and Validation components together.

## Building a form

To a build a form, we must first call the form handler from the service container in your controller, defining how you want to render it:

	$handler = $this->_services['form.handler.php']; // call this to render in php
	$handler = $this->_services['form.handler.twig']; // call this to render in twig

We can now add our fields using the same syntax as Symfony Form, only on the handler instead of directly on the form:

	$handler->add('name', 'text');

Calling the `add()` method automatically adds the field to both the form and the validator, and all fields are required by default. To add further validation, call the `val()` method to call the Validator instance and add more rules:

	$handler->add('name', 'text')
		->val() // call validator
		->capitalize(); // capitalize first letter of each word
	$handler->add('email', 'text')
		->val() // call validator
		->optional() // set field to not required
		->email(); // check that input is a valid email address
	$handler->add('url', 'text')
		->val() // call validator
		->optional() // set field to optional
		->toUrl(); // append 'http://' protocol if not already set

Once we have added all the fields to our form, we can get the completed form using the `getForm()` method:

	$form = $handler->getForm();

You can check that the form has been submitted using the `isPost()` method, and then return it using the `getData()` method like so:

	if ($handler->isPost()) {
		return $handler->getData();
	}

This will return data in an associative array, for instance, a valid form would return something like:

	array (
		'name' => string 'biggie'
		'email' => string 'biggie.smalls@aol.com'
		'url' => 'notorious.com'
	);


## Validation

To validate the form, call the `isValid()` method on the handler:

	// returns boolean
	$handler->isValid();

This will parse the data through the validator, and return true if it passes. If the form is not valid, you can access the error messages using the `getMessages()` method:

	// returns array
	if (!$handler->isValid()) {
		return $handler->getMessages();
	}

This will return an associative array of fields and error messages, if any exist, so using the example above, we could potentially get something like this:

	array(
		'name' => array(
			'Name is a required field.'
		),
		'email' => array(
			'Email must be a valid email address.'
		)
	);

It is also possible to get the data once it has been filtered through the validator, using the `getFilteredData()` method:

	// returns array
	if ($handler->isPost()) {
		return $handler->getFilteredData();
	}

So the data above would return something like this:

	array(
		'name' => 'Biggie', // First letter capitalized as was defined by validator
		'email' => 'biggie@aol.com', // No filtering has taken place as none was defined
		'url' => 'http://notorious.com' // Protocol has been appended to url as defined by validator
	);

## CSRF protection

The form component makes use of Symfony's CSRF extension. These can be customised on a form to form basis in the options of a field. This is the third parameter when creating a new field, e.g.

	$handler->add(
		'name',
		'text',
		array(
			'csrf_protection' => true,
			'csrf_field_name' => '_token',
		)
	);

For more details see the Symfony documentation (http://symfony.com/doc/current/book/forms.html#csrf-protection)

## Rendering

To pass the form to the view, you need to give it an instance of the `FormView` class by calling the `createView()` method:

	return $this->render('::form', array(
    			'form' => $form->createView(),
    		));

You can then follow the documentation on the Symfony site for how to render it in the view (http://symfony.com/doc/current/cookbook/form/form_customization.html)

## Adding custom extensions

There are three main stages to adding a custom extension.

### Creating the custom field types

For each new field type, you will need to create a new class extending the `\Symfony\Component\Form\AbstractType` class. You will need to add a `getName()` method which returns a string containing only lowercase letters, numbers and underscores. This is how the field will be referred to when using the form's `add()` method.

You can also extend existing field types by adding a `getParent()` method. This should return a string containing the name of the field type you want to extend.

### Creating the form extension class

The first is to create a class that extends the `\Symfony\Component\Form\AbstractExtension` class, and add a `loadTypes()` protected method. This method should return an array of instances of each field type within your extension.

You will then need to add this to the form factory builder. You do this via the service container within the module, by extending `form.factory` shared service:

		$serviceContainer['form.factory'] = $serviceContainer->share(
			$serviceContainer->extend('form.factory', function($factory, $c) {
				$factory->addExtensions(array(
					new MyFormExtension // Your new form extension class
				));

				return $factory;
			})
		);

### Creating the view files

For each new field type, you will need to create a view. Within Twig these are all defined in one file (preferably called 'form_div_layout.html.twig'). For PHP, these need to be in separate files, called '[field type name]_widget.html.php'.

You should create a separate directory for each, somewhere within the View folder.

Once the template files have been created, you need to register the references to these by extending 'form.templates.twig' and 'form.templates.php' services in the service container.

		$serviceContainer['form.templates.twig'] = $serviceContainer->extend(
			'form.templates.twig', function($templates, $c) {
			$templates[] = 'Message:Module:Namespace::Form:Twig:form_div_layout';

			return $templates;
		});

		$serviceContainer['form.templates.php'] = $serviceContainer->extend(
			'form.templates.php', function($templates, $c) {
				$templates[] = 'Message:Module:Namespace::Form:Php';

				return $templates;
			}
		);

Note that with Twig you need to reference the file name, but with PHP you need to reference the directory. It's also worth noting that you do not need to reference the View directory.

## Cheat sheet

### Field types

### Validation rules

#### Text

**alnum()** - Check value is alphanumeric

**alpha()** - Check that value is alphabetical

**digit()** - Check that value contains only digits

**length($min, $max)** - Check that value is between two lengths

**minLength($min)** - Check that value is no shorter than a certain length

**maxLength($max)** - Check that value is no longer than a certain length

**email()** - Check that a value is a valid email address

**url()** - Check that a value is a valid URL

**match($regex)** - Check that a value matches a regular expression

#### Numbers

**min($min)** - Check that a value is at least a certain number

**max($max)** - Check that a value is no greater than a certain number

**between($min, $max)** - Check that a value is between two numbers

**multipleOf($number)** - Check that a value is a multiple of a certain number

#### Dates

**before($datetime)** - Check that a value is before a certain DateTime

**after($datetime)** - Check that a value is after a certain DateTime

#### Other

**rule($callback)** - Check value using a callback

### Validation filters

#### Text

**uppercase()** - Make uppercase

**lowercase()** - Make lowercase

**titlecase($maintainCase = false)** - Convert to title case (with minor words non capitalized), use the `$maintainCase` parameter to determine whether to reset the case before filtering

**capitalize($maintainCase = false)** - Capitalize each word in a string, use the `$maintainCase` parameter to determine whether to reset the case before filtering

**prefix($prefix, $delim = '')** - Add text to the start of a string. Use the `$delim` parameter to add a delimeter between the string and the prefix

**suffix($suffix, $delim = '')** - Add text to the end of a string. Use the `$delim` parameter to add a delimeter between the string and the suffix

**trim($chars = null)** - Trim unwanted characters off a string, works the same as the native `trim()` function

**rtrim($chars = null)** - Trim unwanted characters off the end of a string, works the same as the native `rtrim()` function

**ltrim($chars = null)** - Trim unwanted characters off the beginning of a string, works the same as the native `ltrim()` function

**replace($search, $replace)** - Replace instances of `$search` with `$replace`

**toUrl($protocol = 'http', $replaceExisting = false)** - Prepend protocol to string to make it a valid URL. `$protocol` sets the protocol, i.e. https, ftp etc, and `$replaceExisting` determines whether to replace an existing protocol in the submitted string

**slug()** - Convert string to a slug (lowercase and hyphens)

#### Type

**string()** - Convert to string

**int()** - Convert to integer

**integer()** - Convert to integer

**float()** - Convert to float

**bool()** - Convert to boolean

**boolean()** - Convert to boolean

**array()** - Convert to array

**object()** - Convert to instance of stdObject

**date()** - Convert to DateTime

**null()** - Convert to null, if you can find an occasion where you would ever want to do that!

#### Number

**add($value)** - Add `$value` to number

**subtract($value)** - Subtract `$value` from number

**multiple($value)** - Multiply `$value` by number

**divide($value)** - Divide number by `$value`

**percentage($value)** - Get the percentage of submitted value of `$value`

#### Other

**filter($callback)** - Filter the value using a callback