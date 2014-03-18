
Because of major refactoring, this document consists of two parts: `New Form Component` and `Deprecated Form Component`. We use the new form component for new forms and try to slowly move existing forms from the old system to the new one. However, for backwards compatability, we have left the documentation of the deprecated form component in this document.

# New Form Component

We use Symfony Form with custom form extensions. For more resources, have a look at Symfony's documentation ([[1](http://symfony.com/doc/current/book/forms.html)], [[2](http://symfony.com/doc/current/cookbook/form/index.html)], [[3](http://symfony.com/doc/current/reference/forms/types.html)]).

## General Usage
We try to encourage the use of form classes for forms. These extend `Symfony\Component\Form\AbstractType`:

	class FormClass extends \Symfony\Component\Form\AbstractType
	{
	
		public function buildForm(Form\FormBuilderInterface $builder, array $options)
		{

			$builder->add('field_name', 'field_type');
		}

		public function setDefaultOptions(OptionsResolverInterface $resolver)
		{
			$resolver->setDefaults([
				'data_class' => 'Class\\Represented\\By\\FormClass',
			]);
		}

		public function getName()
		{
			return 'form_class';
		}
	}
	
The form is built in the `buildForm()`-method, and needs to have a name (`getName()`).
The method `setDefaultOptions()` defines the default options for the whole form. Probably the most important one of these is `data_class`:
If a `data_class` is given, all form fields will be mapped to the given class. It is therefore important that the field name is the same as the name of the mapped property or the option `property_path` is set on the field.
If a field should not be mapped, add the option `'mapped' => false`:

	$builder->add('field_name', 'field_type' [
		'mapped' => false,
	]);
	$builder->add('field_name2', 'field_type' [
		'property_path' => 'name_of_property_in_underlying_class',
	]);

Other important options of the fields include `label` (label in view) and `data` (data the field should be populated with). The `label`-option also accepts translations:

	$builder->add('field_name', 'field_type' [
		'label' => 'translation.key.for.field', // or just a string like 'Field Name'
	]);

For more informaiton on different form field types and their options, have a look at [Symfony's Form Type Reference](http://symfony.com/doc/current/reference/forms/types.html).

## Validation
For validation we now use Symfony's Validator component. Adding validation to a form is as easy as adding the `constraints`-option:

	use Symfony\Component\Validator\Constraints;
	
	...
	
	$builder->add('field_name', 'field_type' [
		'constraints' => new Constraints\NotBlank
	]);
	
	$builder->add('field_name', 'field_type' [
		'constraints' => [
			new Constraints\GreaterThan(['value' => 0]),
			new Constraints\LessThanOrEqual(['value' => 100]),
		]
	]);

For a full list of all validation constraints refer to [Symfony's Validation Constraints Reference](http://symfony.com/doc/current/reference/constraints.html).

A way of adding custom validation is using [Form Events](http://symfony.com/doc/current/cookbook/form/dynamic_form_modification.html):

	use use Symfony\Component\Form;
	
	...
	
	$builder->addEventListener(Form\FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
	
	...
	
	public function onPostSubmit(Form\FormEvent $event)
	{
		$form = $event->getForm(); // Form\FormInterface
		$model = $form->getData(); // submitted data, already bound to the model
		
		if (...) {
			$form->addError(new Form\FormError('Form Error!'));
		}
		
		$field = $form->get('field_name'); // e.g. for fields that are not mapped
		$fieldData = $field->getData();
		
		if (...) {
			$field->addError(new Form\FormError('Error on field `field_name`'));
		}
	}
	
## Form Extensions

We have added a number of form extensions to the form componenet, many of which are part of our core form funcionality and therefore live in cog. However, in this readme only cog's form extension and details on writing an own form extension are covered.

### Writing an own form extension
Form extensions are basically a way of adding form types, form type extensions (and form type guessers) to the form component.
In cog they can be found in `Form\Extension\` and have to extend `Symfony\Component\Form\AbstractExtension`:

	namespace Message\Cog\Form\Extension\MyExtension;

	use Symfony\Component\Form\AbstractExtension;

	class MyExtension extends Symfony\Component\Form\AbstractExtension
	{
		protected function loadTypes();
		protected function loadTypeExtensions();
	}

The methods `loadTypes()` and `loadTypeExtensions()` (and `loadTypeGuessers()`, which we don't use) return an array of all form types and field type extensions added by the extension.

A form type is basically a representation of a property in a form (e.g. strings, dates, numbers…). One example for these is our Entity Type.

Form type extensions on the other hand extend one or more existing form types (e.g. to add options or functionality). We use them to change the defaults of Date and Time Types (`Message\Cog\Form\Extension\Core\Type\DateTypeExtension`).

For more information check out Symfony's Tutorials on [Custom Form Types](http://symfony.com/doc/current/cookbook/form/create_custom_field_type.html) and [Custom Form Type Extensions](http://symfony.com/doc/current/cookbook/form/create_form_type_extension.html).

To add a form extension to the form component in cog, just add it to the `form.extensions`-service. In other cogules, you can extend the `form.factory.builder`-service:

	$services->extend('form.factory.builder', function($factory, $c) {
		$factory->addExtension(new MyExtension);
		
		// to add more than one at the same time:
		$factory->addExtensions([
			new MyExtension1,
			new MyExtension2,
		]);
		
		return $factory;
	});


### Required Field Extension
Symfony offers the possibility to add HTML5-required-attributes to form fields. Normally using the option `'required' => true/false` is responsible for adding or not adding this attribute.
Because we also use Symfony's Validation component, we decided to add an extension which automatically adds the `required`-option depending on the constraints added to the field.
It should therefore **never be necessary to add the `required`-option manually**.

This works by adding the `Message\Cog\Form\Extension\Validator\Type\FormTypeRequiredExtension`, which analyses the constraints on the form (fields) and sets the `required`-option accordingly.

### Validation messages in flashes / with form fields
Symfony add validation messages directly to the form fields. Because we have former added our validation errors to the flashes, we wanted to have an option of also doing this with the new validation component.
We implemented `Message\Cog\Form\Extension\Validator\Type\FormTypeErrorsWithFieldsExtension`: With an event listener all form errors are collected and added to the flashes if the option `errors_with_fields` is set to `false` (which is the default for now). Otherwise they will be rendered with the form fields:

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'errors_with_fields' => true,
		]);
	}

### Entity Type
We wanted to have a form field type for entity collections, which automatically converts between arrays or collections of objects and a `choice`-type (dropdown, checkboxes or radio buttons).
To do that we implemented the `Message\Cog\Form\Extension\Core\Type\EntityType`. You can use it by just adding a field of type `entity`:

	$builder->add('field_name', 'entity' [
		'choices'  => $this->_choices,
		'property' => 'property used for labelling', \\ defaults to using __toString() of entity
		'value'    => 'unique identifier', \\ defaults to 'id', can be getter or property name
	]);
	
In the background a Choice List (`Message\Cog\Form\Extension\Core\ChoiceList\EntityChoiceList`) is generated from the `choices`-option.
By default, the `id`-property of the entities will be used as an unique identifier for the entities. This behaviour can be changed using the `value`-option, which accepts names of properties, methods and closures.
To display the entity in the form view, the `property`-option is used to determine the display name of the entity. This option defaults to the `__toString()`-method and accepts names of methods or properties and closures.

# Deprecated Form Component

The form integrates the Symfony Form component with the rest of the framework, specifically templating and validation.

Due to the complexity of the Symfony Form component, and the fact that many of the classes have several private properties and methods, the majority of the classes are not extended. As a result, we must use a handler to use the Form and Validation components together.

## Building a form

To a build a form, we must first call the form handler from the service container in your controller:

	$form = $this->_services['form'];

We can now add our fields using the similar syntax to Symfony Form, except with the label as the third parameter, and the array of options as the forth:

	$form->add('name', 'text', 'Name', array());

Calling the `add()` method automatically adds the field to both the form and the validator, and all fields are required by default. To add further validation, call the `val()` method to call the Validator instance and add more rules:

	$form->add('name', 'text')
		->val() // call validator
		->capitalize(); // capitalize first letter of each word
	$form->add('email', 'text')
		->val() // call validator
		->optional() // set field to not required
		->email(); // check that input is a valid email address
	$form->add('url', 'text')
		->val() // call validator
		->optional() // set field to optional
		->toUrl(); // append 'http://' protocol if not already set

For nested Forms just create a new form and add the form to its parent:

	$child
		->add('price', 'money', 'Price GBP', array('currency' => 'GBP'))
		->val() // call validator
		->number(); // applies number()-rule

	$parent->add($child, 'form'); // adds the child form with its validation and all fields to the parent-form

Nested forms will be represented as multi-dimensional arrays in the data.

Once we have added all the fields to our form, we can get the completed form using the `getForm()` method:

	$form = $form->getForm();

You can check that the form has been submitted using the `isPost()` method, and then return it using the `getData()` method like so:

	if ($form->isPost()) {
		return $form->getData();
	}

This will bind the data to the form and then return it in an associative array, for instance, a valid form would return something like:

	array (
		'name' => string 'biggie'
		'email' => string 'biggie.smalls@aol.com'
		'url' => 'notorious.com'
	);

## Validation

To validate the form, call the `isValid()` method on the handler:

	// returns boolean
	$form->isValid();

This will return a boolean determining whether or not the form is valid. It will also add any error messages to the flash bag, unless you give it a parameter of false.

It is also possible to get the data once it has been filtered through the validator, using the `getFilteredData()` method. This method also validates the form and adds messages to the flash bag, unless given a parameter of false.

It is important to note that you must run the `isValid()` method to check that the form is valid, as `getFilteredData()` will always return an array.

The best practice for validating the form is to include both the definition of the filtered data and the validation check within the same if statement like so:

	if ($form->isValid() && $data = $form->getFilteredData()) {
		// your code
	}

`$data` will look like this:

	array(
		'name' => 'Biggie', // First letter capitalized as was defined by validator
		'email' => 'biggie@aol.com', // No filtering has taken place as none was defined
		'url' => 'http://notorious.com' // Protocol has been appended to url as defined by validator
	);

## CSRF protection

The form component makes use of Symfony's CSRF extension. These can be customised on a form to form basis in the options of a form.

	$form->addOptions(array(
		'csrf_protection' => true,
		'csrf_field_name' => '_token',
	));

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

	$serviceContainer->extend('form.factory', function($factory, $c) {
		$factory->addExtensions(array(
			new MyFormExtension // Your new form extension class
		));

		return $factory;
	});

### Creating the view files

For each new field type, you will need to create a view. Within Twig these are all defined in one file (preferably called 'form_div_layout.html.twig'). For PHP, these need to be in separate files, called '[field type name]_widget.html.php'.

You should create a separate directory for each, somewhere within the View folder.

Once the template files have been created, you need to register the references to these by extending 'form.templates.twig' and 'form.templates.php' services in the service container.

	$serviceContainer->extend('form.templates.twig', function($templates, $c) {
		$templates[] = 'Message:Module:Namespace::Form:Twig:form_div_layout';

		return $templates;
	});

	$serviceContainer->extend('form.templates.php', function($templates, $c) {
		$templates[] = 'Message:Module:Namespace::Form:Php';

		return $templates;
	});

Note that with Twig you need to reference the file name, but with PHP you need to reference the directory. It's also worth noting that you do not need to reference the View directory.

## Cheat sheet

### Field types

### Validation rules

#### Type

**number()** - Check whether value is a number or valid numeric string

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