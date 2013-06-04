# Form

The form integrates the Symfony Form component with the rest of the framework, specifically templating and validation.

Due to the complexity of the Symfony Form component, and the fact that many of the classes have several private properties and methods, the majority of the classes are not extended. As a result, we must use a wrapper to use the Form and Validation components together.

## Building a form

To a build a form, we must first call the form wrapper from the service container in your controller:

	$wrapper = $this->_services['form.wrapper.php'];

We can now add our fields using the same syntax as Symfony Form, only on the wrapper instead of directly on the form:

	$wrapper->add('name', 'text);

Calling the `add()` method automatically adds the field to both the form and the validator, and all fields are required by default. To add further validation, call the `val()` method to call the Validator instance and add more rules:

	$wrapper->add('name', 'text')
		->val()
		->optional()
		->capitalize();

Once we have added all the fields to our form, we can get the completed form using the `getForm()` method:

	$form = $wrapper->getForm();

## Validation

To validate the form, call the `isValid()` method on the wrapper:

	// returns boolean
	$wrapper->isValid();

This will parse the data through the validator, and return true if it passes. If the form is not valid, you can access the error messages using the `getMessages()` method;

	// returns array
	$wrapper->getMessages();

It is also possible to get the data once it has been filtered through the validator, using the `getFilteredData()` method:

	// returns array
	$wrapper->getFilteredData();