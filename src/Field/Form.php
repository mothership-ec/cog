<?php

namespace Message\Cog\Field;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Service\ContainerInterface;
use Symfony\Component\Form\FormFactory;

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
	protected $_factory;
	protected $_builder;

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
	public function generate($content)
	{
		$defaultValues  = [];
		$this->_factory = $this->_services['form.factory'];

		if (!is_array($content) && !$content instanceof \Traversable) {
			throw new \Exception('Content must be traversable');
		}

		foreach ($content as $name => $contentPart) {
			if ($contentPart instanceof Field) {
				$defaultValues[$name] = $contentPart->getValue();
			}
		}

		$this->_builder = $this->_factory->createBuilder('form', $defaultValues);

		foreach ($content as $fieldName => $field) {
			if ($field instanceof Group) {
				$this->_addGroup($field);
			} else if ($field instanceof BaseField) {
				$this->_addField($field);
			} else if ($field instanceof RepeatableContainer) {
				$this->_addRepeatableGroup($fieldName, $field);
			}
		}

		return $this->_builder->getForm();
	}

	/**
	 * Add a single field to the form.
	 *
	 * @param BaseField $field
	 */
	protected function _addField(BaseField $field)
	{
		$field->getFormField($this->_builder);
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

		$builder = $this->_factory->createBuilder('form', $values, [
			'name' => $group->getName(),
		]);

		foreach ($group->getFields() as $field) {
			$field->getFormField($builder);
		}

		$this->_builder->add($builder, 'form', [
			'label' => $group->getLabel(),
		]);
	}

	/**
	 * Add a repeatable group of fields to the form.
	 *
	 * @param string              $name  Name of the repeatable group
	 * @param RepeatableContainer $group The repeatable group
	 */
	protected function _addRepeatableGroup($name, RepeatableContainer $group)
	{
//		$groupBuilder = $this->_factory->createNamedBuilder(
//			$name,
//			'form'
//		);

		$dynamic = new DynamicFormType;

		// Add each field as a collection
		foreach ($group->getFields() as $field) {
//			$field->getFormField($groupBuilder);
			$dynamic->add($field->getName(), $field->getFormType(), $field->getFieldOptions());
		}

		// Add the form to the main form
		$this->_builder->add($name, 'collection', [
			'label' => $group->getLabel(),
			'type'  => $dynamic,
			'allow_add' => true,
			'allow_delete' => true,
			'data' => $this->_getDefaultValuesForRepeatableGroup($group)
		]);
	}

	/**
	 * Get an array of default values for a repeatable group.
	 *
	 * This method returns an array of values for each individual group
	 *
	 * <code>
	 * [0]
	 *    [colour] => 'Red'
	 *    [size] => 'Large'
	 * [1]
	 *    [colour] => 'Blue'
	 *    [size] => 'Small'
	 * </code>
	 *
	 * @param  RepeatableContainer $repeatable The repeatable group
	 *
	 * @return array                           Array of default values
	 */
	public function _getDefaultValuesForRepeatableGroup(RepeatableContainer $repeatable)
	{
		$values = [];

		foreach ($repeatable as $i => $group) {
			$groupValues = [];
			foreach ($group->getFields() as $field) {
				$groupValues[$field->getName()] = $field->getValue();
			}
			$values[$i] = $groupValues;
		}

		return $values;
	}
}