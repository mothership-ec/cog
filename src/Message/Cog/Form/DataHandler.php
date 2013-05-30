<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form;
use Message\Cog\Validation\Validator;

/**
 * Class DataHandler
 * @package Message\Cog\Form
 *
 * Class to handle data upon form submission, specifically to check that it is valid
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class DataHandler
{
	/**
	 * @var Form
	 */
	protected $_form;

	/**
	 * @var \Message\Cog\Validation\Validator
	 */
	protected $_validator;

	/**
	 * @param Form $form                Form to assign to data handler
	 * @param Validator $validator      Validator instance to provide validation rules
	 */
	public function __construct(Form $form, Validator $validator = null)
	{
		$this->setForm($form);

		if ($validator) {
			$this->_validator = $validator;
		}
	}

	/**
	 * @param Form $form        Form to provide data
	 *
	 * @return DataHandler      Returns $this for chainability
	 */
	public function setForm(Form $form)
	{
		$this->_form = $form;

		return $this;
	}

	/**
	 * @param Validator $validator      Validator instance to provide validation rules
	 *
	 * @return DataHandler              Returns $this for chainability
	 */
	public function setValidator(Validator $validator)
	{
		$this->_validator = $validator;

		return $this;
	}

	/**
	 * @return Form         Returns assigned form
	 */
	public function getForm()
	{
		return $this->_form;
	}

	/**
	 * @return Validator    Returns assigned validator
	 */
	public function getValidator()
	{
		return $this->_validator;
	}

	/**
	 * @param array $data           Data to be validated, defaults to form's data
	 * @throws \LogicException      Throws exception if method is called before form data has been set
	 *
	 * @return bool                 Returns true if data is valid
	 */
	public function isValid(array $data = null)
	{
		if (!$this->_validator) {
			return true;
		}
		elseif (!$this->_form->isBound() && !$data) {
			throw new \LogicException(__CLASS__ . '::' . __METHOD__ . ' - You cannot call isValid() on a form that is not bound.');
		}

		return ($data) ? $this->_validator->validate($data) : $this->_validator->validate($this->_form->getData());
	}

	/**
	 * Class to return data once it has been filtered through the validator
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
		elseif (!empty($_POST[$this->_form->getName()])) {
			return $this->getPost();
		}

		return array();
	}

	/**
	 * Checks if form data has been submitted
	 *
	 * @return bool         Returns true if form data has been submitted, false if not
	 */
	public function isPost()
	{
		return (!empty($_POST[$this->_form->getName()])) ? true : false;
	}

	/**
	 * Get posted form data
	 *
	 * @return array        Returns posted form data
	 */
	public function getPost()
	{
		return ($this->isPost()) ? $_POST[$this->_form->getName()] : array();
	}

	/**
	 * Get error messages from validator
	 *
	 * @return array        Returns array of error messages, or an empty array if no validator is set
	 */
	public function getMessages()
	{
		return ($this->_validator) ? $this->_validator->getMessages() : array();
	}
}