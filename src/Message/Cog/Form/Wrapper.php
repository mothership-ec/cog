<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form as SymfonyForm;
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
class Wrapper
{
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
	 * @var string
	 */
	protected $_type;

	protected $_defaults = array(
//		'required' => false,
	);


	/**
	 * Creates instance of SymfonyForm and Validator on construction
	 *
	 * @param Container $container      Service container for getting instance of form builder and validation
	 * @param string $type              Type of rendering engine to use, i.e. php or twig
	 */
	public function __construct(Container $container, $type = 'php')
	{
		$this->_type = $type;
		$this->_container = $container;
		$this->_container['templating.php.engine']->addHelpers(array($this->_container['form.helper.' . $type]));
		$this->_form = $this->_container['form.builder']->getForm();
		$this->_validator = $this->_container['validator'];
		$this->_request = $this->_container['request'];
	}

	/**
	 * Replaces instances of form and validator with fresh ones
	 */
	public function clear()
	{
		$this->_form = $this->_container['form.builder']->getForm();
		$this->_validator = $this->_container['validator'];
	}

	/**
	 * Add a field to a form
	 *
	 * @param string | SymfonyForm $child       Name or instance of field, e.g. 'First name'
	 * @param null $type                        Type of field, defaults to text
	 * @param array $options                    Options for field, see Symfony Form documentation
	 * @throws \InvalidArgumentException        Throws exception if $child is not a string or Form object
	 *
	 * @return Wrapper                          Returns $this for chainability
	 */
	public function add($child, $type = null, array $options = array())
	{
		if(!is_string($child) && (!$child instanceof SymfonyForm)) {
			throw new \InvalidArgumentException('$child must be either a string or instance of Symfony\Component\Form\Form');
		}

		$options = array_merge($this->_defaults, $options);

		$this->_form->add($child, $type, $options);
		$this->_validator->field($this->_getChildName($child));

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
	 * @return Wrapper                Returns $this for chainability
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
	 * @return Wrapper                  Returns $this for chainability
	 */
	public function setValidator(Validator $validator)
	{
		$this->_validator = $validator;

		return $this;
	}

	/**
	 * Get instance of SymfonyForm
	 *
	 * @return SymfonyForm         Returns assigned form
	 */
	public function getForm()
	{
		return $this->_form;
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
	 * @param bool $fromPost        If set to true, data is taken from posted form data, instead of bound data
	 * @param array $data           Data to be validated, defaults to form's data
	 * @throws \LogicException      Throws exception if method is called before form data has been set
	 *
	 * @return bool                 Returns true if data is valid
	 */
	public function isValid($fromPost = false, array $data = null)
	{
		if (!$this->_form->isBound() && !$data && !$fromPost) {
			throw new \LogicException('You cannot call isValid() on a form that is not bound, unless $fromPost is set to true, or $data is set');
		}
		elseif ($fromPost) {
			$data = $this->getPost();
		}

		return ($data) ? $this->_validator->validate($data) : $this->_validator->validate($this->_form->getData());
	}

	/**
	 * Method to return data once it has been filtered through the validator
	 *
	 * @param array $data       Data to be validated, defaults to form's data
	 *
	 * @return array            Returns filtered data
	 */
	public function getFilteredData(array $data = null)
	{
		if (!$data) {
			$data = $this->getData();
		}

		$this->_validator->validate($data);

		return $this->_validator->getData();
	}

	/**
	 * Get data submitted to form
	 *
	 * @return array    Returns data submitted to form
	 */
	public function getData()
	{
		if ($this->_form->isBound()) {
			return $this->_form->getData();
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
		$post = $this->_request->get($this->_form->getName());
		return (!empty($post)) ? true : false;
	}

	/**
	 * Get posted form data
	 *
	 * @return array        Returns posted form data
	 */
	public function getPost()
	{
		$post = $this->_request->get($this->_form->getName());
		return ($post) ? $post : array();
	}

	/**
	 * Get error messages from validator
	 *
	 * @return array        Returns array of error messages, or an empty array if no validator is set
	 */
	public function getMessages()
	{
		return $this->_validator->getMessages();
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

}