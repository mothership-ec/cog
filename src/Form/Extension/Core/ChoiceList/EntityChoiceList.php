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
	 * all available choices
	 * @var array
	 */
	protected $_choices;

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
		$this->_choices          = $choices;
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
			foreach ($this->_choices as $j => $choice) {
				if ($givenChoice && $this->_propertyAccessor->getValue($choice, $this->_identifier)
					=== $this->_propertyAccessor->getValue($givenChoice, $this->_identifier)) {
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
}
