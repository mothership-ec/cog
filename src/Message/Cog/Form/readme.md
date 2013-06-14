# Form

The form integrates the Symfony Form component with the rest of the framework, specifically templating and validation.

Due to the complexity of the Symfony Form component, and the fact that many of the classes have several private properties and methods, the majority of the classes are not extended. As a result, we must use a creator to use the Form and Validation components together.

## Building a form

To a build a form, we must first call the form creator from the service container in your controller, defining how you want to render it:

	$creator = $this->_services['form.creator.php']; // call this to render in php
	$creator = $this->_services['form.creator.twig']; // call this to render in twig

We can now add our fields using the same syntax as Symfony Form, only on the creator instead of directly on the form:

	$creator->add('name', 'text');

Calling the `add()` method automatically adds the field to both the form and the validator, and all fields are required by default. To add further validation, call the `val()` method to call the Validator instance and add more rules:

	$creator->add('name', 'text')
		->val() // call validator
		->capitalize(); // capitalize first letter of each word
	$creator->add('email', 'text')
		->val() // call validator
		->optional() // set field to not required
		->email(); // check that input is a valid email address
	$creator->add('url', 'text')
		->val() // call validator
		->optional() // set field to optional
		->toUrl(); // append 'http://' protocol if not already set

Once we have added all the fields to our form, we can get the completed form using the `getForm()` method:

	$form = $creator->getForm();

You can check that the form has been submitted using the `isPost()` method, and then return it using the `getData()` method like so:

	if ($creator->isPost()) {
		return $creator->getData();
	}

This will return data in an associative array, for instance, a valid form would return something like:

	array (
		'name' => string 'biggie'
		'email' => string 'biggie.smalls@aol.com'
		'url' => 'notorious.com'
	);


## Validation

To validate the form, call the `isValid()` method on the creator:

	// returns boolean
	$creator->isValid();

This will parse the data through the validator, and return true if it passes. If the form is not valid, you can access the error messages using the `getMessages()` method:

	// returns array
	if (!$creator->isValid()) {
		return $creator->getMessages();
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
	if ($creator->isPost()) {
		return $creator->getFilteredData();
	}

So the data above would return something like this:

	array(
		'name' => 'Biggie', // First letter capitalized as was defined by validator
		'email' => 'biggie@aol.com', // No filtering has taken place as none was defined
		'url' => 'http://notorious.com' // Protocol has been appended to url as defined by validator
	);

## Rendering

To pass the form to the view, you need to give it an instance of the `FormView` class by calling the `createView()` method:

	return $this->render('::form', array(
    			'form' => $form->createView(),
    		));

You can then follow the documentation on the Symfony site for how to render it in the view (http://symfony.com/doc/current/cookbook/form/form_customization.html)