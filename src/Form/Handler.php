<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form as SymfonyForm;
use Message\Cog\Validation\Field;
use Message\Cog\Validation\Validator;
use Message\Cog\Service\Container;

/**
 * Class DataHandler
 * @package Message\Cog\Form
 *
 * Class to tie a form to a validator. It is not easy/possible to extend some of Symfony's form classes due to the
 * use of private properties and methods, as well as the labyrinthian structure of the component. This class is
 * designed to create an instance of the form and of the validator, and allow them to work together.
 *
 * @todo when adding a select field, make sure validation removes any fields that aren't in the list
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Handler
{
	// Constant is hard coded in form_start twig method, if you edit this you will need to edit that as well
	const CSRF_ATTRIBUTE_NAME = '_csrf_form';

	/**
	 * @var \Message\Cog\Service\Container
	 */
	protected $_container;

	/**
	 * @var SymfonyForm
	 */
	protected $_form;

	/**
	 * @var \Message\Cog\Validation\Validator
	 */
	protected $_validator;

	/**
	 * @var \Symfony\Component\Form\FormBuilder
	 */
	protected $_builder;

	/**
	 * @var string
	 */
	protected $_type;

	/**
	 * @var array
	 */
	protected $_defaultValues = array();

	/**
	 * @var string
	 */
	protected $_name = 'form';

	/**
	 * @var array
	 */
	protected $_options = array(
		'required' => true,
		'csrf_protection' => true,
		'csrf_field_name' => self::CSRF_ATTRIBUTE_NAME,
		'csrf_message' => 'It seems something went wrong, please refresh / re-open your page and try again.',
		'intention' => 'form'
	);

	/**
	 * @var bool
	 */
	protected $_repeatable = false;

	/**
	 * @var bool
	 */
	protected $_addedToFlash = false;

	/**
	 * @var bool | null
	 */
	protected $_valid = null;

	/**
	 * @var array all fields to be added to the form
	 */
	protected $_fields = array();

	/**
	 * @var array a list of all the fields and their types
	 */
	protected $_fieldList	= array();

	/**
	 * @var \Symfony\Component\Form\FormFactory
	 */
	protected $_factory;

	protected $_initialised	= false;


	/**
	 * Creates instance of SymfonyForm and Validator on construction
	 *
	 * @param Container $container      Service container for getting instance of form builder and
	 *                                  validation
	 */
	public function __construct(Container $container)
	{
		$this->_container   = $container;

		$this->_factory     = $this->_container['form.factory']->getFormFactory();
		$this->_builder     = $this->_container['form.builder'];
		$this->_validator   = $this->_container['validator'];
		$this->_request     = $this->_container['request'];

		$this->_container['templating.engine.php']
			->addHelpers(array(
				$this->_container['form.helper.twig'],
				$this->_container['form.helper.php'],
			));
	}

	/**
	 * Set the name of the form
	 *
	 * @param string $name      Name of form
	 *
	 * @return Handler          Return $this for chainability
	 */
	public function setName($name)
	{
		$this->_name = $name;
		$this->_options['intention'] = $name;

		return $this;
	}

	/**
	 * Set the default values for the form
	 *
	 * @param array $values     Set default values for form
	 *
	 * @return Handler          Returns $this for chainability
	 */
	public function setDefaultValues($values)
	{
		if ($this->_initialised) {
			throw new \Exception('You cannot add default values to an initialised form!');
		}
		$this->_defaultValues = (array) $values;

		return $this;
	}

	/**
	 * Set the method for the form
	 *
	 * @param string $method        Method for form
	 * @throws \LogicException      Throws exception if form has already been instanciated
	 *
	 * @return Handler              Returns $this for chainability
	 */
	public function setMethod($method)
	{
		if ($this->_form) {
			throw new \LogicException('You cannot set the method for a form that has already been instanciated');
		}

		$this->_options['method'] = $method;

		return $this;
	}

	/**
	 * Add default options
	 *
	 * @param array $options
	 *
	 * @return Handler
	 */
	public function addOptions(array $options)
	{
		$this->_options = array_merge($this->_options, $options);

		return $this;
	}

	/**
	 * Set the action for the form
	 *
	 * @param string $action        Action for form
	 * @throws \LogicException      Throws exception if form has already been instanciated
	 *
	 * @return Handler              Return $this for chainability
	 */
	public function setAction($action)
	{
		if ($this->_form) {
			throw new \LogicException('You cannot set the action for a form that has already been instanciated');
		}

		$this->_options['action'] = $action;

		return $this;
	}

	/**
	 * Set whether the fields generated should be repeatable fields (called
	 * 'collections' within Symfony).
	 *
	 * This is disabled by default.
	 *
	 * @param boolean $bool True to enable, false to disable
	 */
	public function setRepeatable($bool = true)
	{
		$this->_repeatable = (bool) $bool;

		return $this;
	}

	/**
	 * Replaces instances of form and validator with fresh ones
	 */
	public function clear()
	{
		$this->_form            = $this->_container['form.builder']->getForm();
		$this->_fields          = array();
		$this->_validator       = $this->_container['validator'];
		$this->_addedToFlash    = false;
		$this->_valid           = null;
	}

	/**
	 * Adds a field to the $_fields-array to then be added to $_form
	 *
	 * @param string | SymfonyForm | Handler $child Name or instance of field, e.g. 'First name'
	 * @param null $type                            Type of field, defaults to text
	 * @param string $label                         Label for the field, if null it's auto generated from the name
	 * @param array $options                        Options for field, see Symfony Form documentation
	 * @throws \InvalidArgumentException            Throws exception if $child is not a string or Form object
	 *
	 * @return Handler                              Returns $this for chainability
	 */
	public function add($child, $type = null, $label = null, array $options = array())
	{
		$handler = null;

		if($child instanceof Handler) {
			$handler = $child;
			$child   = $handler->getForm();
		}

		if(!is_string($child) && (!$child instanceof SymfonyForm)) {
			throw new \InvalidArgumentException(
				'The child you tried to add to the form doesn\'t have the right type!'
			);
		}

		if ($label) {
			$options = array_merge(array('label' => $label), $options);
		}

		$options = array_merge($this->_options, $options);

		$field = array(
			'child'   => $child,
			'type'    => $type,
			'options' => $options,
		);

		$fieldName = $this->_getChildName($child);
		$fieldLabel = (isset($options['label']) ? $options['label'] : false);
		$this->_fields[$fieldName] = $field;
		$this->_fieldList[$fieldName]	= $type ?: 'text';

		$validatorField = new Field($fieldName, $fieldLabel);
		if($handler) {
			$validatorField->children = $handler->getValidator()->getFields();
		}

		$validatorField->repeatable = $this->_repeatable;
		$this->getValidator()->addField($validatorField);

		return $this;
	}

	/**
	 * Get a list of all registered fields, and their types
	 *
	 * @return array
	 */
	public function getFieldList()
	{
		return $this->_fieldList;
	}

	/**
	 * Get array of fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * @param $child
	 * @param $type
	 * @param $options
	 *
	 * @return Handler
	 */
	protected function _addCollection($child, $type, $options)
	{
		$this->_form->add($child, 'collection', array(
			'type'         => $type,
			'allow_add'    => true,
			'allow_delete' => true,
			'prototype'    => true,
			'options'      => $options,
		));

		return $this;
	}

	/**
	 * Gets instance of validator. Identical to getValidator() method, only this follows a naming convention for when
	 * creating forms, rather than simply grabbing an instance of the validator
	 *
	 * @return Validator
	 */
	public function val()
	{
		return $this->getValidator();
	}

	/**
	 * Get a field, defaults to the most recently added
	 *
	 * @param string | null $name                               Name of field to retrieve
	 * @throws \LogicException                                  Throws exception if no fields have been added
	 * @throws \Exception                                       Throws exception if child does not exist
	 *
	 * @return \Symfony\Component\Form\FormInterface            Returns requested field or last field
	 */
	public function field($name = null)
	{
		$formChildren = $this->getForm()->all();

		if (!count($formChildren)) {
			throw new \LogicException('No fields added to form!');
		}
		elseif ($name && !array_key_exists($name, $formChildren)) {
			throw new \Exception('Child \'' . $name . '\' does not exist!');
		}

		return $name ? $formChildren[$name] : end($formChildren);
	}

	/**
	 * Inject an instance of SymfonyForm
	 *
	 * @param SymfonyForm $form       New instance of form
	 *
	 * @return Handler                Returns $this for chainability
	 */
	public function setForm(SymfonyForm $form)
	{
		$this->_form = $form;

		return $this;
	}

	/**
	 * Inject instance of Validator
	 *
	 * @param Validator $validator      Validator instance to provide validation rules
	 *
	 * @return Handler                  Returns $this for chainability
	 */
	public function setValidator(Validator $validator)
	{
		$this->_validator = $validator;

		return $this;
	}

	/**
	 * Returns instance of SymfonyForm and fills it with fields from $_fields.
	 * Checks whether all $_fields are already in the SymfonyForm and adds
	 * them if necessary.
	 *
	 * @return SymfonyForm         Returns assigned form
	 */
	public function getForm()
	{
		if(!$this->_form) {
			$this->_form = $this->_initialiseForm();
		}

		$formFields = $this->_form->all();
		foreach($this->_fields as $name => &$fieldArray) {
			if(!array_key_exists($name, $formFields)) {
				$this->_addFieldToSymfonyForm($fieldArray);
			}
		}

		return $this->_form;
	}

	/**
	 * Add a field (in form of an array) to $_form
	 * Sets the required option to what is defined in the validator.
	 *
	 * @param array $fieldArray Array consisting of child, type and options(inc. label) for the field
	 *
	 * @return Handler          Returns $this for chainability
	 */
	protected function _addFieldToSymfonyForm(&$fieldArray)
	{
		$validatorField = $this->getValidator()->getField($this->_getChildName($fieldArray['child']));
		$fieldArray['options']['required'] = !$validatorField->optional;

		if ($this->_repeatable) {
			$this->_addCollection($fieldArray['child'], $fieldArray['type'], $fieldArray['options']);
		}
		else {
			$this->_form->add($fieldArray['child'], $fieldArray['type'], $fieldArray['options']);
		}
	}

	protected function _initialiseForm()
	{
		$form = $this->_factory->createNamed($this->_name, 'form', $this->_defaultValues, $this->_options);
		$this->_initialised	= true;

		return $form;
	}

	public function getBuilder()
	{
		return $this->_builder;
	}

	/**
	 * Get instance of Validator
	 *
	 * @return Validator    Returns assigned validator
	 */
	public function getValidator()
	{
		return $this->_validator;
	}

	/**
	 * Check submitted data for validator. You can toggle whether the data validated is bound to the form, or if
	 * it is posted. You can also submit your own array of data, although this will be overwritten if $fromPost
	 * is set to to true
	 *
	 * @return bool                 Returns true if data is valid
	 */
	public function isValid($addToFlash = true)
	{
		// Ensure validation is not run twice as this can cause field data to go missing second time round
		if ($this->_valid === null) {

			if(!$this->getPost()) {
				$this->_valid = false;

				return $this->_valid;
			}

			$this->submitForm();

			// because symfony removes invalid data, but we actually need this data for the validator
			// to display the right error messages, we need to give the validator an array with both the
			// new symfony-form-data and the post-data :'(
			$data = array_replace_recursive($this->getPost(), $this->getForm()->getData());

			$valid = $this->_validator->validate($data);
			$valid = ($valid) ? $this->getForm()->isValid() : $valid;

			if ($addToFlash && !$this->_addedToFlash) {
				$this->addMessagesToFlash();
			}

			$this->_valid = $valid && $this->getForm()->isValid();
		}

		return $this->_valid;
	}

	/**
	 * Add error messages to flash bag
	 *
	 * @return Handler
	 */
	public function addMessagesToFlash()
	{
		$messages = $this->getMessages();

		foreach($messages as $message) {
			$this->_container['http.session']->getFlashBag()->add('error', $message);
		}

		$this->_addedToFlash = true;

		return $this;
	}

	/**
	 * Binds posted data to form
	 *
	 * @return Handler      Returns $this for chainability
	 */
	public function submitForm()
	{
		// try and bind it to a request if it's been posted.
		if(!$this->getForm()->isSubmitted() && $data = $this->getPost()) {
			$this->getForm()->submit($data);
		}

		return $this;
	}

	/**
	 * Method to return data once it has been filtered through the validator
	 *
	 * @param array $data       Data to be validated, defaults to form's data
	 * @param bool $addToFlash  Have flash messages already been added to the flash bag???
	 *
	 * @return array            Returns filtered data
	 */
	public function getFilteredData($addToFlash = true)
	{
		$this->isValid($addToFlash);

		// here we want the filtered data from the validator but don't need to include the
		// data symfony removed because it doesn't match the type
		$data = $this->_validator->getData();

		// Sometimes date fields do not automatically get formatted to a
		// Datetime object due to browser differences. This ensures all dates
		// are instantiated correctly.
		foreach ($this->_fields as $name => $field) {
			if (in_array($field['type'], ['date', 'datetime']) and $value = $data[$name] and ! $value instanceof \DateTime) {
				// If the value is an array it's date and time separately, so smush them together
				if (is_array($value)) {
					$value = trim(implode(' ', $value));
				}

				$format = $this->_container['helper.date']->detectFormat($value);

				// The ! character is important as it tells DateTime to default
				// to epoch values where they are not defined in the value (i.e. if time is not set, default to 00:00:00)
				$data[$name] = \DateTime::createFromFormat('!' . $format, $value);
			}
		}

		return $data;
	}

	/**
	 * Get data submitted to form
	 *
	 * @return array    Returns data submitted to form
	 */
	public function getData()
	{
		if ($this->getForm()->isSubmitted()) {
			return $this->getForm()->getData();
		}

		return $this->getPost();
	}

	/**
	 * Checks if form data has been submitted
	 *
	 * @return bool         Returns true if form data has been submitted, false if not
	 */
	public function isPost()
	{
		$post = $this->_request->get($this->getForm()->getName());
		return (!empty($post)) ? true : false;
	}

	/**
	 * Get posted form data
	 *
	 * @return array        Returns posted form data
	 */
	public function getPost()
	{
		$post = $this->_request->get($this->getForm()->getName());

		return ($post) ? $post : array();
	}

	/**
	 * Get error messages from validator and form
	 * Note: Most of the messages will come from the form, but some validation is handled by the form, specifically
	 * the CSRF token
	 *
	 * @return array        Returns array of error messages, or an empty array if no validator is set
	 */
	public function getMessages()
	{
		$messages = array();

		foreach($this->_validator->getMessages() as $field) {
			foreach($field as $message) {
				$messages[] = $message;
			}
		}

		return array_merge($messages, $this->_getFormErrors());

	}

	/**
	 * Checks if the form child given is an instance of SymfonyForm, and returns name if so. Otherwise it casts the
	 * param to a string and returns that
	 *
	 * @param string | SymfonyForm $child       Name of child, or instance of child field
	 *
	 * @return string
	 */
	protected function _getChildName($child) {
		if ($child instanceof SymfonyForm) {
			return $child->getName();
		}

		return (string) $child;
	}

	/**
	 * Retrieve error messages caused by the form
	 *
	 * @return array
	 */
	protected function _getFormErrors()
	{
		$errors = $this->getForm()->getErrors();

		foreach ($errors as $key => &$error) {
			$errors[$key] = $error->getMessage();
		}

		return $errors;
	}

}