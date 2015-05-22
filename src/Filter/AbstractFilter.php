<?php

namespace Message\Cog\Filter;

use Message\Cog\DB\QueryBuilderInterface;

/**
 * Class AbstractFilter
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Abstract class for basic functionality of a filter. Handles setting of names and options, as well as
 * taking the assumption that the form will be a standard checkbox field
 */
abstract class AbstractFilter implements FilterInterface
{
	/**
	 * @var string
	 */
	private $_name;

	/**
	 * @var string
	 */
	private $_displayName;

	/**
	 * @var mixed
	 */
	protected $_value;

	/**
	 * @var array
	 */
	protected $_options = [
		'multiple' => true,
		'expanded' => true,
	];

	/**
	 * Set the name and display name on instanciation, and set the label of the form field to
	 * the display name
	 *
	 * @param $name
	 * @param $displayName
	 */
	public function __construct($name, $displayName)
	{
		$this->_setName($name);
		$this->_setDisplayName($displayName);

		$this->_options['label'] = $this->getDisplayName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName()
	{
		return $this->_displayName;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Default to 'choice' field
	 */
	public function getForm()
	{
		return 'choice';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOptions(array $options)
	{
		$this->_options = $options + $this->_options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value)
	{
		$this->_value = $value;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception\NoValueSetException   Throws an exception if no value is set against the filter
	 *                                         when applying to query builder
	 */
	public function apply(QueryBuilderInterface $queryBuilder)
	{
		if (null === $this->_value) {
			throw new Exception\NoValueSetException('Cannot apply a filter when no value is set');
		}

		$this->_applyFilter($queryBuilder);
	}

	/**
	 * Method for applying changes to main query
	 *
	 * @param QueryBuilderInterface $queryBuilder
	 */
	abstract protected function _applyFilter(QueryBuilderInterface $queryBuilder);

	/**
	 * Validate the type for the name and then set it
	 *
	 * @param $name
	 * @throws \InvalidArgumentException   Throws exception if $name is not a string
	 */
	protected function _setName($name)
	{
		if (!is_string($name)) {
			throw new \InvalidArgumentException('First parameter must be a string, ' . gettype($name) . ' given');
		}

		$this->_name = $name;
	}
	/**
	 * Validate the type for the display name and then set it
	 *
	 * @param $displayName
	 * @throws \InvalidArgumentException   Throws exception if $displayName is not a string
	 */
	protected function _setDisplayName($displayName)
	{
		if (!is_string($displayName)) {
			throw new \InvalidArgumentException('Second parameter must be a string, ' . gettype($displayName) . ' given');
		}

		$this->_displayName = $displayName;
	}
}