<?php

namespace Message\Cog\Form\Extension\Core\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Extension of Symfony's ObjectChoiceList to enable matching of values and
 * arrays via their $identifier rather than using '==='.
 *
 * This was an issue because already selected choices in the model were not
 * checked/selected in the form
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class EntityChoiceList extends ObjectChoiceList
{
	/**
	 * Unique identifier for the entity
	 */
	protected $_identifier;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(
		$choices,
		$labelPath = null,
		array $preferredChoices = array(),
		$groupPath = null,
		$valuePath = null,
		PropertyAccessorInterface $propertyAccessor = null)
	{
		$this->_propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
		$this->_identifier       = null !== $valuePath ? new PropertyPath($valuePath) : null;

		parent::__construct($choices, $labelPath, $preferredChoices, $groupPath, $valuePath, $propertyAccessor);
	}

	/**
	 * This method is used by the form to determine checked/selected
	 * options.
	 *
	 * {@inheritdoc}
	 */
	public function getValuesForChoices(array $choices)
	{
		$choices = $this->fixChoices($choices);
		$values = array();

		foreach ($choices as $i => $givenChoice) {
			foreach ($this->getChoices() as $j => $choice) {
				if ($this->areChoicesEqual($givenChoice, $choice)) {
					$values[$i] = $this->getValues()[$j];
					unset($choices[$i]);

					if (0 === count($choices)) {
						break 2;
					}
				}
			}
		}

		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIndicesForChoices(array $choices)
	{
		$choices = $this->fixChoices($choices);
		$indices = array();

		foreach ($choices as $i => $givenChoice) {
			foreach ($this->getChoices() as $j => $choice) {
				if ($this->areChoicesEqual($givenChoice, $choice)) {
					$indices[$i] = $j;
					unset($choices[$i]);

					if (0 === count($choices)) {
						break 2;
					}
				}
			}
		}

		return $indices;
	}

	/**
	 * Method comparing two choices.
	 *
	 * @param  mixed   $choice1   First choice
	 * @param  mixed   $choice2   Second choice
	 * @return boolean            True if the first and second choice have the same
	 *                            $_identifier-property (and are therefore considered equal).
	 */
	protected function areChoicesEqual($choice1, $choice2)
	{
		// make sure both choices are not null, then compare their values
		return $choice1 && $choice2
			&& $this->_propertyAccessor->getValue($choice1, $this->_identifier)
			=== $this->_propertyAccessor->getValue($choice2, $this->_identifier);
	}
}
