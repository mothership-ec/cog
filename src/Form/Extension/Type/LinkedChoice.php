<?php

namespace Message\Cog\Form\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Two choice menus that have some sort of inherit link.
 *
 * @todo Figure out how to map the names to the values
 */
class LinkedChoice extends AbstractType
{
	protected $_groups;

	/**
	 * @todo the way this works is pretty out of step with the rest of the form component as it takes an array argument
	 * for its constructor. This was originally in CMS but was copied over with the fields stuff (as the productoption
	 * field type needs it). The constructor argument means that you can't register it in the same way that you can with
	 * the other form types
	 */
	public function __construct(array $choiceGroups)
	{
		$this->_groups = $choiceGroups;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		foreach ($this->_groups as $name => $choices) {
			// Cannot use array-merge as we need to preserve any integer keys.
			$choiceData = ['none' => 'None'];
			foreach ($choices as $key => $value) {
				$choiceData[$key] = $value;
			}

			$builder->add($name, 'choice', array(
				'choices'     => $choiceData,
				'empty_value' => 'Please select...',
			));
		}
	}

	public function getName()
	{
		return 'linked_choice';
	}
}