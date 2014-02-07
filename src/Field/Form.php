<?php

namespace Message\Cog\Field;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Form\Handler;
use Message\Cog\Service\ContainerInterface;

/**
 * Generates a form for given content fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author James Moss <james@message.co.uk>
 */
class Form
{
	protected $_services;
	protected $_content;
	protected $_form;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $services The service container
	 */
	public function __construct(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Generate fields for a form for given content fields.
	 *
	 * @param  Handler $form    The form to generate the fields for
	 * @param  Content $content The content fields to use
	 *
	 * @return Symfony\Component\Form\Form The generated form
	 */
	public function generate(Handler $form, Content $content)
	{
		$defaultValues = array();
		foreach ($content as $name => $contentPart) {
			if ($contentPart instanceof Field) {
				$defaultValues[$name] = $contentPart->getValue();
			}
		}

		$this->_form = $form
			->setValidator($content->getValidator())
			->setDefaultValues($defaultValues);

		foreach ($content as $fieldName => $field) {
			if ($field instanceof Group) {
				$this->_addGroup($field);
			} else if ($field instanceof BaseField) {
				$this->_addField($field);
			} else if ($field instanceof RepeatableContainer) {
				$this->_addRepeatableGroup($fieldName, $field);
			}
		}


		return $this->_form;
	}

	/**
	 * Add a single field to the form.
	 *
	 * @param BaseField $field
	 */
	protected function _addField(BaseField $field)
	{
		$field->getFormField($this->_form);
	}

	/**
	 * Add a group of fields to the form.
	 *
	 * @param Group $group
	 */
	protected function _addGroup(Group $group)
	{
		$values = array();

		foreach ($group->getFields() as $name => $field) {
			$values[$name] = $field->getValue();
		}

		$groupHandler = $this->_services['form']
			->setName($group->getName())
			->setDefaultValues($values)
			->setValidator($group->getValidator())
			->addOptions(array(
				'auto_initialize' => false,
			));

		foreach ($group->getFields() as $fieldName => $field) {
			$field->getFormField($groupHandler);
		}

		$this->_form->add($groupHandler, 'form', $group->getLabel());
	}

	/**
	 * Add a repeatable group of fields to the form.
	 *
	 * @param string              $name  Name of the repeatable group
	 * @param RepeatableContainer $group The repeatable group
	 */
	protected function _addRepeatableGroup($name, RepeatableContainer $group)
	{
		// Create form for group
		$groupHandler = $this->_services['form']
			->setName($name)
			->setRepeatable()
			->setValidator($group->getValidator())
			->setDefaultValues($this->_getDefaultValuesForRepeatableGroup($group))
			->addOptions(array(
				'auto_initialize' => false,
			));

		// Add each field as a collection
		foreach ($group->getFields() as $fieldName => $field) {
			$field->getFormField($groupHandler);
		}

		// Add the form to the main form
		$this->_form->add($groupHandler, 'form', $group->getLabel());
	}

	/**
	 * Get an array of default values for a repeatable group.
	 *
	 * Because of the way the Form component expects these values (grouped by
	 * field), the array needs to be structured as field => array of values.
	 *
	 * So, consider a repeatable group with the fields "title" and "colour", the
	 * returned array would look something like:
	 *
	 * <code>
	 * [title]
	 *    [0] => 'This colour is red'
	 *    [1] => 'This colour is blue'
	 * [colour]
	 *    [0] => 'Red'
	 *    [1] => 'Blue'
	 * </code>
	 *
	 * @param  RepeatableContainer $repeatable The repeatable group
	 *
	 * @return array                           Array of default values
	 */
	public function _getDefaultValuesForRepeatableGroup(RepeatableContainer $repeatable)
	{
		$values = array();

		foreach ($repeatable as $i => $group) {
			foreach ($group->getFields() as $field) {
				$values[$field->getName()][$i] = $field->getValue();
			}
		}

		return $values;
	}
}